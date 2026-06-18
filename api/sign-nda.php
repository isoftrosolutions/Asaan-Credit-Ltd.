<?php
http_response_code(410);
echo json_encode(['ok' => false, 'error' => 'NDA signing has been removed.']);
