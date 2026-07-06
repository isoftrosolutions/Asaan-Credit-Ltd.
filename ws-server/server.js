const { WebSocketServer } = require('ws');
const http = require('http');
const crypto = require('crypto');
const mysql = require('mysql2/promise');

const WS_PORT = 8080;
const HTTP_PORT = 8081;
const WS_SECRET = 'asaan-capital-ws-secret-2026';
const DB_CONFIG = {
  host: '127.0.0.1',
  port: 3306,
  user: 'root',
  password: '',
  database: 'asaancapital_assan_capital',
  charset: 'utf8mb4',
};

const users = new Map(); // userId → Set<WebSocket>

function verifyToken(userId, token) {
  const expected = crypto.createHmac('sha256', WS_SECRET).update(String(userId)).digest('hex');
  return crypto.timingSafeEqual(Buffer.from(expected), Buffer.from(token));
}

function broadcastToUser(userId, data) {
  const conns = users.get(String(userId));
  if (!conns) return;
  const msg = JSON.stringify(data);
  for (const ws of conns) {
    if (ws.readyState === 1) {
      ws.send(msg);
    }
  }
}

async function start() {
  const pool = mysql.createPool(DB_CONFIG);

  const wss = new WebSocketServer({ port: WS_PORT });
  console.log(`WS server listening on port ${WS_PORT}`);

  wss.on('connection', (ws, req) => {
    let userId = null;
    let authenticated = false;

    ws.on('message', async (raw) => {
      try {
        const data = JSON.parse(raw.toString());

        switch (data.type) {
          case 'auth': {
            const uid = String(data.user_id);
            if (!verifyToken(uid, data.token)) {
              ws.send(JSON.stringify({ type: 'auth_error', message: 'Invalid token' }));
              return;
            }
            userId = uid;
            authenticated = true;
            if (!users.has(userId)) users.set(userId, new Set());
            users.get(userId).add(ws);
            ws.send(JSON.stringify({ type: 'auth_ok', user_id: userId }));
            console.log(`User ${userId} connected`);
            break;
          }

          case 'typing': {
            if (!authenticated) return;
            const { conversation_id, recipient_id } = data;
            broadcastToUser(String(recipient_id), {
              type: 'typing',
              conversation_id,
              user_id: userId,
            });
            break;
          }

          case 'stop_typing': {
            if (!authenticated) return;
            const { conversation_id, recipient_id } = data;
            broadcastToUser(String(recipient_id), {
              type: 'stop_typing',
              conversation_id,
              user_id: userId,
            });
            break;
          }

          case 'mark_read': {
            if (!authenticated) return;
            const { conversation_id } = data;
            try {
              await pool.execute(
                'UPDATE conversation_participants SET last_read_at = NOW() WHERE conversation_id = ? AND user_id = ?',
                [conversation_id, userId]
              );
            } catch (e) {
              console.error('mark_read error:', e.message);
            }
            break;
          }

          case 'ping': {
            ws.send(JSON.stringify({ type: 'pong' }));
            break;
          }
        }
      } catch (e) {
        console.error('WS message error:', e.message);
      }
    });

    ws.on('close', () => {
      if (userId && users.has(userId)) {
        users.get(userId).delete(ws);
        if (users.get(userId).size === 0) users.delete(userId);
        console.log(`User ${userId} disconnected`);
      }
    });

    ws.on('error', () => {});
  });

  // Internal HTTP server for PHP to push notifications
  const httpServer = http.createServer(async (req, res) => {
    if (req.method !== 'POST') {
      res.writeHead(405);
      res.end();
      return;
    }

    let body = '';
    req.on('data', (chunk) => (body += chunk));
    req.on('end', () => {
      try {
        const data = JSON.parse(body);
        const path = req.url;

        if (path === '/send-message') {
          const { recipient_id, conversation_id, message, sender_id, created_at, message_id, sender_name } = data;
          broadcastToUser(String(recipient_id), {
            type: 'new_message',
            conversation_id,
            message_id,
            message,
            sender_id,
            sender_name,
            created_at,
          });
          res.writeHead(200, { 'Content-Type': 'application/json' });
          res.end(JSON.stringify({ ok: true }));
        } else if (path === '/conversation-updated') {
          // Notify participant that conversation list changed
          const { user_id, conversation_id, last_message, last_message_at, other_user } = data;
          broadcastToUser(String(user_id), {
            type: 'conversation_updated',
            conversation_id,
            last_message,
            last_message_at,
            other_user,
          });
          res.writeHead(200, { 'Content-Type': 'application/json' });
          res.end(JSON.stringify({ ok: true }));
        } else {
          res.writeHead(404);
          res.end();
        }
      } catch (e) {
        console.error('HTTP error:', e.message);
        res.writeHead(400);
        res.end(JSON.stringify({ error: e.message }));
      }
    });
  });

  httpServer.listen(HTTP_PORT, () => {
    console.log(`HTTP notification server listening on port ${HTTP_PORT}`);
  });
}

start().catch((e) => {
  console.error('Startup error:', e);
  process.exit(1);
});
