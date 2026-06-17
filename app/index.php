<?php
/**
 * Divya Jyotish - Download Landing Page
 *
 * Auto-discovers installer files in the same directory and maps them to
 * Windows / macOS / Linux. Reads electron-builder's latest.yml manifests
 * for version, release date, and file metadata.
 *
 * Just drop this file beside your installers — no configuration needed.
 *
 * Place at:  ~/app.divyajyotish.com/public_html/index.php
 * Beside:    DivyaJyotish-Setup-1.0.1.exe, latest.yml,
 *            DivyaJyotish-1.0.1.dmg, latest-mac.yml, etc.
 */

// ============================================================
//  CONFIG — edit these only if needed
// ============================================================
$APP_NAME       = 'Divya Jyotish';
$APP_NAME_HI    = 'दिव्य ज्योतिष';
$APP_TAGLINE    = 'Authentic Vedic astrology, crafted for the desktop';
$SUPPORT_EMAIL  = 'support@divyajyotish.com';
$BRAND_YEAR     = date('Y');

// File patterns to look for (case-insensitive)
$PLATFORM_PATTERNS = [
    'win' => [
        'extensions' => ['exe'],
        'yml'        => 'latest.yml',
        'label'      => 'Windows',
        'spec'       => 'Windows 10 or 11 · 64-bit',
        'ext_label'  => '.exe',
    ],
    'mac' => [
        'extensions' => ['dmg', 'pkg', 'zip'],
        'yml'        => 'latest-mac.yml',
        'label'      => 'macOS',
        'spec'       => 'macOS 11+ · Intel & Apple Silicon',
        'ext_label'  => '.dmg',
        'troubleshooting' => [
            'title' => 'Which version do I need?',
            'steps' => [
                'Check which Mac you have — Apple Menu → About This Mac',
                'If it says <strong>Chip: Apple M…</strong> → download the ARM64 build',
                'If it says <strong>Processor: Intel</strong> → download the x64 build',
            ],
            'fix_title' => 'App won\'t open after moving to Applications?',
            'fix_desc' => 'macOS flagged the app because it\'s unsigned. Run this in Terminal:',
            'fix_cmd' => 'xattr -cr /Applications/Divya\\ Jyotish.app',
        ],
    ],
];

// ============================================================
//  HELPERS
// ============================================================

/**
 * Minimal YAML parser tuned for electron-builder's latest.yml format.
 * Returns associative array of metadata.
 */
function parse_simple_yaml($text) {
    $result = [];
    $lines  = preg_split("/\r?\n/", $text);
    $currentList = null;
    $currentItem = null;

    foreach ($lines as $rawLine) {
        $line = rtrim($rawLine);
        if (trim($line) === '' || strpos(trim($line), '#') === 0) continue;

        // List item line:  - url: foo.exe
        if (preg_match('/^\s*-\s+(\w+):\s*(.+)$/', $line, $m)) {
            // Save previous item to its list
            if ($currentList !== null && $currentItem !== null) {
                $result[$currentList][] = $currentItem;
            }
            $currentItem = [$m[1] => trim($m[2])];
            continue;
        }

        // Indented continuation of list item:    size: 12345
        if (preg_match('/^\s{2,}(\w+):\s*(.+)$/', $line, $m) && $currentItem !== null) {
            $currentItem[$m[1]] = trim($m[2]);
            continue;
        }

        // Top-level key
        if (preg_match('/^(\w+):\s*(.*)$/', $line, $m)) {
            // Flush any in-progress list item
            if ($currentList !== null && $currentItem !== null) {
                $result[$currentList][] = $currentItem;
                $currentItem = null;
            }
            $key = $m[1];
            $val = trim($m[2]);
            if ($val === '') {
                $result[$key] = [];
                $currentList  = $key;
            } else {
                $result[$key] = $val;
                $currentList  = null;
            }
        }
    }
    // Flush final list item
    if ($currentList !== null && $currentItem !== null) {
        $result[$currentList][] = $currentItem;
    }
    return $result;
}

/**
 * Format bytes as human-readable size.
 */
function format_size($bytes) {
    if (!$bytes || !is_numeric($bytes)) return null;
    $bytes = (float)$bytes;
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576)    return number_format($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024)       return number_format($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

/**
 * Format ISO date as "Jan 15, 2026"
 */
function format_date_human($iso) {
    if (!$iso) return null;
    $ts = strtotime($iso);
    if (!$ts) return null;
    return date('M j, Y', $ts);
}

/**
 * Detect CPU architecture from a filename.
 * Checks for arm64, universal, x64 markers.
 */
function detect_architecture($filename) {
    $lower = strtolower($filename);
    if (preg_match('/\b(arm64|aarch64)\b/', $lower)) return ['key' => 'arm64', 'label' => 'Apple Silicon (ARM64)', 'badge' => 'arm64'];
    if (preg_match('/\b(universal)\b/', $lower)) return ['key' => 'universal', 'label' => 'Universal (Intel & Apple Silicon)', 'badge' => 'universal'];
    if (preg_match('/\b(x64|x86_64|amd64|intel)\b/', $lower)) return ['key' => 'x64', 'label' => 'Intel (x64)', 'badge' => 'x64'];
    return ['key' => 'standard', 'label' => 'Standard', 'badge' => 'standard'];
}

/**
 * Try to extract a semver version string from a filename.
 */
function parse_version_from_filename($filename) {
    if (preg_match('/(\d+\.\d+\.\d+(?:[.-][a-zA-Z0-9]+)?)/', $filename, $m)) {
        return $m[1];
    }
    return null;
}

/**
 * Scan the directory for files matching the given extensions.
 * Returns array of ['name' => ..., 'size' => ..., 'mtime' => ...].
 */
function scan_for_files($dir, $extensions) {
    $found = [];
    if (!is_dir($dir)) return $found;
    $items = @scandir($dir);
    if (!$items) return $found;

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (!is_file($path)) continue;

        $lower = strtolower($item);
        foreach ($extensions as $ext) {
            $extLower = strtolower($ext);
            // Match suffix (handles .tar.gz too)
            if (substr($lower, -strlen($extLower) - 1) === '.' . $extLower) {
                $found[] = [
                    'name'  => $item,
                    'size'  => filesize($path),
                    'mtime' => filemtime($path),
                ];
                break;
            }
        }
    }

    // Sort newest first
    usort($found, function($a, $b) { return $b['mtime'] - $a['mtime']; });
    return $found;
}

/**
 * Detect visitor OS from User-Agent.
 * Returns 'windows', 'mac', 'linux', 'mobile', or 'unknown'.
 */
function detect_visitor_os() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (preg_match('/Android|iPhone|iPad|iPod/i', $ua))                 return 'mobile';
    if (preg_match('/Windows/i', $ua))                                  return 'windows';
    if (preg_match('/Macintosh|Mac OS X/i', $ua))                       return 'mac';
    if (preg_match('/Linux/i', $ua))                                    return 'linux';
    return 'unknown';
}

// ============================================================
//  BUILD PLATFORM DATA — now collects ALL files per platform
//  with architecture detection from filenames
// ============================================================

$BASE_DIR = __DIR__;
$platforms = [];
$globalVersion = null;
$globalReleaseDate = null;
$allVersions = [];

foreach ($PLATFORM_PATTERNS as $key => $cfg) {
    $platform = [
        'key'              => $key,
        'label'            => $cfg['label'],
        'spec'             => $cfg['spec'],
        'ext_label'        => $cfg['ext_label'],
        'troubleshooting'  => $cfg['troubleshooting'] ?? null,
        'available'        => false,
        'files'            => [],
        'version'          => null,
        'release_date'     => null,
    ];

    // Try the YML manifest first (most authoritative)
    $ymlPath = $BASE_DIR . DIRECTORY_SEPARATOR . $cfg['yml'];
    $meta = null;
    if (is_file($ymlPath)) {
        $ymlContent = @file_get_contents($ymlPath);
        if ($ymlContent !== false) {
            $meta = parse_simple_yaml($ymlContent);
        }
    }

    // Collect files from YAML manifest
    $ymlFiles = [];
    if ($meta) {
        $platform['version'] = $meta['version'] ?? null;
        $platform['release_date'] = $meta['releaseDate'] ?? null;

        // electron-builder stores files in a "files" list or single "path"
        if (!empty($meta['files']) && is_array($meta['files'])) {
            foreach ($meta['files'] as $entry) {
                $fn = $entry['url'] ?? null;
                if ($fn) {
                    $candidate = $BASE_DIR . DIRECTORY_SEPARATOR . $fn;
                    if (is_file($candidate)) {
                        $ymlFiles[$fn] = [
                            'filename' => $fn,
                            'size'     => isset($entry['size']) ? (int)$entry['size'] : filesize($candidate),
                            'sha512'   => $entry['sha512'] ?? null,
                            'version'  => $platform['version'],
                        ];
                    }
                }
            }
        } elseif (!empty($meta['path'])) {
            $fn = $meta['path'];
            $candidate = $BASE_DIR . DIRECTORY_SEPARATOR . $fn;
            if (is_file($candidate)) {
                $ymlFiles[$fn] = [
                    'filename' => $fn,
                    'size'     => $meta['size'] ?? filesize($candidate),
                    'sha512'   => $meta['sha512'] ?? null,
                    'version'  => $platform['version'],
                ];
            }
        }
    }

    // Scan directory for all matching files (catches any file not in YAML)
    $scanned = scan_for_files($BASE_DIR, $cfg['extensions']);

    // Merge: YAML files first (authoritative metadata), then scanned extras
    $seen = [];
    foreach ($ymlFiles as $fn => $data) {
        $arch = detect_architecture($fn);
        $ver = $data['version'] ?: parse_version_from_filename($fn);
        $platform['files'][] = [
            'filename'   => $fn,
            'size'       => $data['size'],
            'size_human' => format_size($data['size']),
            'version'    => $ver,
            'sha512'     => $data['sha512'],
            'arch'       => $arch,
        ];
        $seen[$fn] = true;
    }

    foreach ($scanned as $sf) {
        if (isset($seen[$sf['name']])) continue;
        $arch = detect_architecture($sf['name']);
        $ver = $platform['version'] ?: parse_version_from_filename($sf['name']);
        $platform['files'][] = [
            'filename'   => $sf['name'],
            'size'       => $sf['size'],
            'size_human' => format_size($sf['size']),
            'version'    => $ver,
            'sha512'     => null,
            'arch'       => $arch,
        ];
    }

    if (!empty($platform['files'])) {
        $platform['available'] = true;
    }

    // Track global version/release across platforms
    if ($platform['version'] && !$globalVersion) {
        $globalVersion = $platform['version'];
    }
    if ($platform['release_date'] && !$globalReleaseDate) {
        $globalReleaseDate = $platform['release_date'];
    }

    // Collect all unique versions for the version list
    foreach ($platform['files'] as $f) {
        if ($f['version']) $allVersions[$f['version']] = true;
    }

    $platforms[$key] = $platform;
}

// Remove Linux platform entirely
unset($platforms['linux']);

// Split macOS into two architecture-specific cards
$macBase = $platforms['mac'] ?? null;
$macArm64Files = [];
$macX64Files = [];
if ($macBase) {
    foreach ($macBase['files'] ?? [] as $f) {
        if ($f['arch']['key'] === 'x64') {
            $macX64Files[] = $f;
        } else {
            $macArm64Files[] = $f;
        }
    }
    $platforms['mac-arm64'] = [
        'key' => 'mac-arm64', 'label' => 'macOS (Apple Silicon)',
        'spec' => 'Apple M1/M2/M3/M4 chips · macOS 11+',
        'ext_label' => $macBase['ext_label'],
        'troubleshooting' => $macBase['troubleshooting'],
        'available' => !empty($macArm64Files),
        'files' => $macArm64Files,
        'version' => $macBase['version'], 'release_date' => $macBase['release_date'],
    ];
    $platforms['mac-x64'] = [
        'key' => 'mac-x64', 'label' => 'macOS (Intel)',
        'spec' => 'Intel processors · macOS 11+',
        'ext_label' => $macBase['ext_label'],
        'troubleshooting' => null,
        'available' => !empty($macX64Files),
        'files' => $macX64Files,
        'version' => $macBase['version'], 'release_date' => $macBase['release_date'],
    ];
    unset($platforms['mac']);
}

// Card display order — always 3 columns
$cardKeys = ['win', 'mac-arm64', 'mac-x64'];

$visitorOS = detect_visitor_os();

// Recommendation logic
$recommended = null;
if ($visitorOS === 'windows' && $platforms['win']['available']) $recommended = 'win';
elseif ($visitorOS === 'mac') {
    // Check if macOS is available (any variant), prefer ARM64
    $anyMacAvail = ($platforms['mac-arm64']['available'] ?? false) || ($platforms['mac-x64']['available'] ?? false);
    if ($anyMacAvail) {
        $recommended = ($platforms['mac-arm64']['available'] ?? false) ? 'mac-arm64' : 'mac-x64';
    }
}

$detectionMessage = '';
if ($visitorOS === 'windows') {
    $detectionMessage = $platforms['win']['available']
        ? 'Detected: <strong>Windows</strong> — your installer is ready below'
        : 'Detected: <strong>Windows</strong> — Windows version is preparing';
} elseif ($visitorOS === 'mac') {
    $detectionMessage = ($platforms['mac-arm64']['available'] ?? false) || ($platforms['mac-x64']['available'] ?? false)
        ? 'Detected: <strong>macOS</strong> — your installer is ready below'
        : 'Detected: <strong>macOS</strong> — Mac version coming soon';
} elseif ($visitorOS === 'mobile') {
    $detectionMessage = 'Mobile detected — Divya Jyotish is a <strong>desktop application</strong>. Please visit from a Mac or Windows PC.';
} else {
    $detectionMessage = 'Choose your platform below';
}

$displayVersion = $globalVersion ?: '—';
$displayDate    = format_date_human($globalReleaseDate) ?: 'recently';

// Helper to safely echo
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($APP_NAME) ?> — Authentic Vedic Astrology for Desktop</title>
<meta name="description" content="Professional Vedic astrology desktop application. Twenty modules including Gochar, Kundali, Prashna, Panchang, and Vivah Milan.">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700&family=Inter+Tight:wght@400;500;600;700&family=Tiro+Devanagari+Hindi&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
:root {
  --cream:        #FAF6EE;
  --cream-warm:   #F5EDDC;
  --paper:        #FFFFFF;
  --ink:          #1C1814;
  --ink-soft:     #4A4138;
  --ink-mute:     #7A6E60;
  --line:         #E8DFCE;
  --gold:         #B8893A;
  --gold-deep:    #8B6422;
  --saffron:      #D4661F;
  --maroon:       #7A2828;
  --green-tilak:  #2D5F3F;

  --shadow-sm: 0 1px 2px rgba(28, 24, 20, 0.04), 0 1px 3px rgba(28, 24, 20, 0.06);
  --shadow-md: 0 4px 12px rgba(28, 24, 20, 0.06), 0 8px 24px rgba(28, 24, 20, 0.04);
  --shadow-lg: 0 12px 32px rgba(28, 24, 20, 0.08), 0 24px 64px rgba(28, 24, 20, 0.06);
  --shadow-gold: 0 8px 24px rgba(184, 137, 58, 0.18), 0 16px 48px rgba(184, 137, 58, 0.12);

  --r-sm: 8px; --r-md: 14px; --r-lg: 20px;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body {
  font-family: 'Inter Tight', -apple-system, BlinkMacSystemFont, sans-serif;
  font-size: 16px; line-height: 1.6;
  color: var(--ink); background: var(--cream);
  -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;
  overflow-x: hidden;
}
.bg-ornament { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
.bg-ornament::before {
  content: ''; position: absolute; top: -200px; right: -300px;
  width: 800px; height: 800px;
  background: radial-gradient(circle, rgba(212, 102, 31, 0.06) 0%, transparent 60%);
  border-radius: 50%;
}
.bg-ornament::after {
  content: ''; position: absolute; bottom: -300px; left: -200px;
  width: 700px; height: 700px;
  background: radial-gradient(circle, rgba(184, 137, 58, 0.05) 0%, transparent 60%);
  border-radius: 50%;
}
nav {
  position: sticky; top: 0; z-index: 100;
  background: rgba(250, 246, 238, 0.85);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border-bottom: 1px solid transparent;
  transition: border-color 0.3s ease, background 0.3s ease;
}
nav.scrolled { border-bottom-color: var(--line); background: rgba(250, 246, 238, 0.95); }
.nav-inner {
  max-width: 1200px; margin: 0 auto; padding: 18px 32px;
  display: flex; align-items: center; justify-content: space-between;
}
.brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: var(--ink); }
.brand-mark {
  width: 36px; height: 36px;
  display: flex; align-items: center; justify-content: center;
  background: linear-gradient(135deg, var(--gold) 0%, var(--gold-deep) 100%);
  border-radius: 10px; color: var(--paper);
  font-family: 'Tiro Devanagari Hindi', serif; font-size: 20px; line-height: 1; padding-top: 2px;
}
.brand-text { display: flex; flex-direction: column; line-height: 1.1; }
.brand-text-en { font-family: 'Fraunces', serif; font-weight: 600; font-size: 18px; letter-spacing: -0.01em; }
.brand-text-hi { font-family: 'Tiro Devanagari Hindi', serif; font-size: 13px; color: var(--ink-mute); }
.nav-links { display: flex; gap: 36px; align-items: center; }
.nav-links a { font-size: 14px; font-weight: 500; color: var(--ink-soft); text-decoration: none; transition: color 0.2s ease; }
.nav-links a:hover { color: var(--gold-deep); }
.nav-cta {
  padding: 10px 20px; background: var(--ink); color: var(--paper) !important;
  border-radius: var(--r-sm); transition: background 0.2s ease;
}
.nav-cta:hover { background: var(--gold-deep); color: var(--paper) !important; }
@media (max-width: 720px) { .nav-links a:not(.nav-cta) { display: none; } }

.hero { position: relative; z-index: 1; max-width: 1200px; margin: 0 auto; padding: 80px 32px 40px; text-align: center; }
.hero-eyebrow {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 6px 14px; background: var(--cream-warm);
  border: 1px solid var(--line); border-radius: 100px;
  font-size: 12px; font-weight: 600; color: var(--ink-soft);
  letter-spacing: 0.04em; text-transform: uppercase; margin-bottom: 24px;
}
.hero-eyebrow-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--green-tilak); box-shadow: 0 0 0 3px rgba(45, 95, 63, 0.15); }
.hero h1 {
  font-family: 'Fraunces', serif; font-weight: 500;
  font-size: clamp(40px, 6vw, 72px); line-height: 1.05;
  letter-spacing: -0.025em; color: var(--ink);
  margin-bottom: 20px; max-width: 900px; margin-left: auto; margin-right: auto;
}
.hero h1 em { font-style: italic; color: var(--saffron); font-weight: 400; }
.hero-devanagari { font-family: 'Tiro Devanagari Hindi', serif; font-size: clamp(18px, 2.5vw, 24px); color: var(--ink-soft); margin-bottom: 16px; }
.hero-sub { font-size: 18px; color: var(--ink-mute); max-width: 620px; margin: 0 auto 40px; line-height: 1.6; }

.detection-banner {
  display: inline-flex; align-items: center; gap: 10px;
  padding: 10px 18px; background: var(--paper);
  border: 1px solid var(--line); border-radius: 100px;
  box-shadow: var(--shadow-sm); font-size: 14px;
  color: var(--ink-soft); margin-bottom: 48px;
}
.detection-banner svg { width: 18px; height: 18px; flex-shrink: 0; }
.detection-banner strong { color: var(--ink); font-weight: 600; }

.download-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  max-width: 1100px;
  margin: 0 auto; position: relative; z-index: 1;
}
@media (max-width: 960px) { .download-grid { grid-template-columns: 1fr; } }

.download-card {
  position: relative; background: var(--paper);
  border: 1px solid var(--line); border-radius: var(--r-lg);
  padding: 36px 32px 32px; text-align: left;
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); overflow: hidden;
}
.download-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); border-color: var(--gold); }
.download-card.recommended {
  border-color: var(--gold); box-shadow: var(--shadow-gold);
  background: linear-gradient(180deg, #FFFCF6 0%, var(--paper) 100%);
}
.download-card.unavailable { opacity: 0.78; }

.recommended-pill {
  position: absolute; top: 16px; right: 16px;
  display: none; align-items: center; gap: 6px;
  padding: 5px 12px; background: var(--ink); color: var(--cream);
  border-radius: 100px; font-size: 11px; font-weight: 600;
  letter-spacing: 0.05em; text-transform: uppercase;
}
.recommended-pill svg { width: 12px; height: 12px; }
.download-card.recommended .recommended-pill { display: inline-flex; }

.os-logo { width: 48px; height: 48px; margin-bottom: 28px; color: var(--ink); }
.download-card h3 {
  font-family: 'Fraunces', serif; font-size: 26px; font-weight: 600;
  letter-spacing: -0.01em; margin-bottom: 6px; color: var(--ink);
}
.os-spec { font-size: 14px; color: var(--ink-mute); margin-bottom: 24px; }
.file-info {
  display: flex; align-items: center; gap: 12px;
  padding: 14px 16px; background: var(--cream);
  border: 1px solid var(--line); border-radius: var(--r-sm); margin-bottom: 24px;
}
.file-info-icon {
  width: 32px; height: 32px; background: var(--paper);
  border: 1px solid var(--line); border-radius: 6px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; color: var(--gold-deep);
}
.file-info-icon svg { width: 16px; height: 16px; }
.file-info-text { flex: 1; min-width: 0; }
.file-info-name {
  font-family: 'JetBrains Mono', monospace; font-size: 12px;
  font-weight: 500; color: var(--ink);
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.file-info-meta { font-size: 11px; color: var(--ink-mute); margin-top: 2px; }
.btn-download {
  display: flex; align-items: center; justify-content: center; gap: 10px;
  width: 100%; padding: 14px 24px;
  background: var(--ink); color: var(--paper);
  border: none; border-radius: var(--r-sm);
  font-family: 'Inter Tight', sans-serif; font-size: 15px; font-weight: 600;
  text-decoration: none; cursor: pointer; transition: all 0.3s ease;
}
.btn-download:hover { background: var(--ink-soft); transform: translateY(-1px); box-shadow: var(--shadow-md); }
.btn-download svg { width: 16px; height: 16px; }
.download-card.recommended .btn-download {
  background: linear-gradient(135deg, var(--gold) 0%, var(--gold-deep) 100%);
  box-shadow: 0 4px 16px rgba(184, 137, 58, 0.3);
}
.download-card.recommended .btn-download:hover { box-shadow: 0 8px 24px rgba(184, 137, 58, 0.4); }
.btn-download.disabled {
  background: var(--cream-warm); color: var(--ink-mute);
  cursor: not-allowed; pointer-events: none;
}

.trust-strip {
  position: relative; z-index: 1; max-width: 920px;
  margin: 48px auto 0;
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: 16px; padding: 24px 0; border-top: 1px solid var(--line);
}
@media (max-width: 720px) { .trust-strip { grid-template-columns: repeat(2, 1fr); gap: 24px 16px; } }
.trust-item { text-align: center; font-size: 13px; color: var(--ink-soft); }
.trust-item svg { width: 20px; height: 20px; color: var(--gold-deep); margin-bottom: 8px; }
.trust-item-label { font-weight: 600; color: var(--ink); font-size: 13px; }
.trust-item-meta { font-size: 12px; color: var(--ink-mute); margin-top: 2px; }

.section { position: relative; z-index: 1; max-width: 1200px; margin: 0 auto; padding: 100px 32px; }
.section-header { text-align: center; margin-bottom: 60px; }
.section-eyebrow { font-size: 13px; font-weight: 600; color: var(--gold-deep); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 16px; }
.section h2 {
  font-family: 'Fraunces', serif; font-size: clamp(32px, 5vw, 52px);
  font-weight: 500; letter-spacing: -0.02em; color: var(--ink);
  line-height: 1.1; margin-bottom: 16px;
}
.section h2 em { font-style: italic; color: var(--saffron); font-weight: 400; }
.section-sub { font-size: 18px; color: var(--ink-mute); max-width: 600px; margin: 0 auto; }
.features-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
@media (max-width: 960px) { .features-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 520px) { .features-grid { grid-template-columns: 1fr; } }
.feature { padding: 28px 24px; background: var(--paper); border: 1px solid var(--line); border-radius: var(--r-md); transition: all 0.3s ease; }
.feature:hover { border-color: var(--gold); transform: translateY(-2px); box-shadow: var(--shadow-md); }
.feature-hi { font-family: 'Tiro Devanagari Hindi', serif; font-size: 22px; color: var(--saffron); margin-bottom: 4px; line-height: 1.2; }
.feature-en { font-family: 'Fraunces', serif; font-size: 18px; font-weight: 600; color: var(--ink); margin-bottom: 10px; }
.feature-desc { font-size: 14px; color: var(--ink-mute); line-height: 1.5; }

footer { position: relative; z-index: 1; background: var(--ink); color: var(--cream); padding: 60px 32px 32px; margin-top: 60px; }
.footer-inner { max-width: 1200px; margin: 0 auto; }
.footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 48px; margin-bottom: 48px; }
@media (max-width: 720px) { .footer-grid { grid-template-columns: 1fr 1fr; gap: 32px; } }
.footer-brand-text { margin-top: 16px; font-size: 14px; color: rgba(245, 237, 220, 0.7); max-width: 320px; line-height: 1.6; }
.footer-col h4 { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: var(--gold); margin-bottom: 16px; }
.footer-col ul { list-style: none; }
.footer-col li { margin-bottom: 10px; }
.footer-col a { font-size: 14px; color: rgba(245, 237, 220, 0.7); text-decoration: none; transition: color 0.2s ease; }
.footer-col a:hover { color: var(--cream); }
.footer-bottom { border-top: 1px solid rgba(245, 237, 220, 0.1); padding-top: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; font-size: 13px; color: rgba(245, 237, 220, 0.5); }
.sanskrit-blessing { font-family: 'Tiro Devanagari Hindi', serif; color: var(--gold); font-size: 14px; }

/* === Play App Section === */
:root { --ease-out: cubic-bezier(0.23, 1, 0.32, 1); }
.app-content {
  display: grid; grid-template-columns: 360px 1fr; gap: 64px;
  align-items: center; max-width: 1000px; margin: 0 auto;
}
@media (max-width: 860px) { .app-content { grid-template-columns: 1fr; gap: 48px; } }
.phone-wrap { display: flex; justify-content: center; }
.phone {
  width: 260px; height: 520px; background: var(--ink);
  border-radius: 36px; padding: 10px; position: relative;
  box-shadow: 0 24px 80px rgba(28, 24, 20, 0.18), 0 8px 24px rgba(28, 24, 20, 0.1);
  transition: transform 0.4s var(--ease-out);
}
.phone:hover { transform: translateY(-4px); }
.phone-screen {
  width: 100%; height: 100%;
  background: linear-gradient(160deg, #2D1F0E 0%, #1C1814 100%);
  border-radius: 27px; overflow: hidden; position: relative;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
}
.phone-om { font-family: 'Tiro Devanagari Hindi', serif; font-size: 48px; color: var(--gold); opacity: 0.85; margin-bottom: 12px; }
.phone-line { width: 40px; height: 2px; background: var(--gold); opacity: 0.2; border-radius: 1px; margin-bottom: 16px; }
.phone-label { font-size: 10px; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(255,255,255,0.3); font-weight: 500; }
.phone-notch {
  position: absolute; top: 10px; left: 50%; transform: translateX(-50%);
  width: 100px; height: 20px; background: #1C1814; border-radius: 0 0 12px 12px;
}
.phone-indicator {
  position: absolute; bottom: 7px; left: 50%; transform: translateX(-50%);
  width: 110px; height: 4px; background: rgba(255,255,255,0.12); border-radius: 2px;
}
.ph-star {
  position: absolute; width: 3px; height: 3px; border-radius: 50%;
  background: var(--gold); opacity: 0.25;
}
.ph-star:nth-child(2) { top: 16%; left: 22%; }
.ph-star:nth-child(3) { top: 22%; right: 18%; }
.ph-star:nth-child(4) { top: 55%; left: 16%; }
.ph-star:nth-child(5) { top: 62%; right: 22%; }
.ph-star:nth-child(6) { top: 38%; right: 30%; }

.app-desc { font-size: 16px; color: var(--ink-mute); line-height: 1.7; margin-bottom: 28px; max-width: 500px; }
.app-feats { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 32px; }
@media (max-width: 480px) { .app-feats { grid-template-columns: 1fr; } }
.app-feat {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 14px; background: var(--paper);
  border: 1px solid var(--line); border-radius: var(--r-sm);
  font-size: 13px; color: var(--ink-soft); transition: all 0.3s var(--ease-out);
  line-height: 1.3;
}
.app-feat:hover { border-color: var(--gold); transform: translateX(4px); }
.app-feat-chk {
  width: 18px; height: 18px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  background: var(--green-tilak); border-radius: 50%;
  color: var(--paper);
}
.app-feat-chk svg { width: 10px; height: 10px; }
.play-btn {
  display: inline-flex; align-items: center; gap: 12px;
  padding: 14px 28px; background: var(--ink); color: var(--paper);
  border-radius: var(--r-sm); text-decoration: none;
  font-size: 15px; font-weight: 600; letter-spacing: 0.01em;
  transition: all 0.3s var(--ease-out); width: fit-content;
}
.play-btn:hover { background: var(--ink-soft); transform: translateY(-2px); box-shadow: var(--shadow-md); }
.play-btn:active { transform: scale(0.97); }
.play-btn svg { width: 20px; height: 20px; }

/* === Multiple-file list & architecture badges === */
.file-list { display: flex; flex-direction: column; gap: 16px; margin-bottom: 20px; }
.file-entry { background: var(--cream); border: 1px solid var(--line); border-radius: var(--r-md); padding: 16px; transition: border-color 0.2s ease; }
.file-entry:hover { border-color: var(--gold); }
.file-entry-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; gap: 8px; flex-wrap: wrap; }
.file-version { font-size: 12px; font-weight: 500; color: var(--ink-mute); font-family: 'JetBrains Mono', monospace; }
.arch-badge {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 3px 10px; border-radius: 100px;
  font-size: 11px; font-weight: 600; letter-spacing: 0.02em;
  text-transform: uppercase; border: 1px solid transparent;
}
.arch-badge::before {
  content: ''; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0;
}
.arch-arm64 { background: #E6F7EE; color: #1B6B3F; border-color: #9ED9B3; }
.arch-arm64::before { background: #2D7D4A; }
.arch-x64 { background: #E6F0FA; color: #1A4D8C; border-color: #9BBEE8; }
.arch-x64::before { background: #2563EB; }
.arch-universal { background: #FEF3E2; color: #8B5E1A; border-color: #F5C77A; }
.arch-universal::before { background: #D97706; }
.arch-standard { background: #F0E6F6; color: #5B2D70; border-color: #C9A0DC; }
.arch-standard::before { background: #7C3AED; }
.file-entry .file-info { margin-bottom: 12px; }
.file-entry .file-info-name { font-size: 11px; }
.file-entry .btn-download { font-size: 13px; padding: 10px 16px; }

/* === Troubleshooting panel === */
.troubleshoot { margin-top: 20px; padding: 18px; background: var(--cream-warm); border: 1px solid var(--line); border-radius: var(--r-md); font-size: 13px; }
.troubleshoot h4 { font-family: 'Fraunces', serif; font-size: 15px; font-weight: 600; margin-bottom: 10px; color: var(--ink); }
.troubleshoot ol { margin: 0 0 14px 18px; color: var(--ink-soft); }
.troubleshoot li { margin-bottom: 5px; line-height: 1.5; }
.troubleshoot li strong { color: var(--ink); font-weight: 600; }
.troubleshoot-fix { margin-top: 10px; padding-top: 12px; border-top: 1px solid var(--line); }
.troubleshoot-fix strong { display: block; margin-bottom: 4px; color: var(--ink); font-size: 13px; }
.troubleshoot-fix p { color: var(--ink-soft); margin-bottom: 8px; }
.troubleshoot-fix code {
  display: block; padding: 10px 14px; background: var(--ink); color: #C5E0B4;
  border-radius: 8px; font-family: 'JetBrains Mono', monospace; font-size: 12px;
  overflow-x: auto; white-space: pre-wrap; word-break: break-all;
}
</style>
</head>
<body>

<div class="bg-ornament"></div>

<nav id="topnav">
  <div class="nav-inner">
    <a href="#" class="brand">
      <span class="brand-mark">ॐ</span>
      <span class="brand-text">
        <span class="brand-text-en"><?= h($APP_NAME) ?></span>
        <span class="brand-text-hi"><?= h($APP_NAME_HI) ?></span>
      </span>
    </a>
    <div class="nav-links">
      <a href="#features">Modules</a>
      <a href="#download">Download</a>
      <a href="#mobile-app">Mobile App</a>
      <a href="mailto:<?= h($SUPPORT_EMAIL) ?>">Support</a>
      <a href="https://www.divyajyotish.com/" class="nav-cta">Login</a>
    </div>
  </div>
</nav>

<section class="hero" id="download">
  <div class="hero-eyebrow">
    <span class="hero-eyebrow-dot"></span>
    Version <?= h($displayVersion) ?> available now
  </div>

  <p class="hero-devanagari">गोचर · प्रश्न · कुंडली · पंचांग · विवाह मिलान</p>

  <h1>Authentic Vedic astrology, <em>crafted for the desktop</em></h1>

  <p class="hero-sub">
    Twenty professional modules — from Gochar and Kundali to Prashna and Vivah Milan. Built for astrologers who demand precision, speed, and tradition.
  </p>

  <div class="detection-banner">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="10"/>
      <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/>
      <path d="M2 12h20"/>
    </svg>
    <span><?= $detectionMessage /* contains safe HTML */ ?></span>
  </div>

  <div class="download-grid">

    <?php foreach ($cardKeys as $pk): ?>
    <?php $p = $platforms[$pk]; ?>
    <div class="download-card<?= $recommended === $pk ? ' recommended' : '' ?><?= $p['available'] ? '' : ' unavailable' ?>">
      <span class="recommended-pill">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4-6.2-4.6-6.2 4.6 2.4-7.4L2 9.4h7.6z"/></svg>
        Recommended for you
      </span>

      <?php if ($pk === 'win'): ?>
      <svg class="os-logo" viewBox="0 0 88 88" xmlns="http://www.w3.org/2000/svg">
        <path fill="#F25022" d="M0 0h42v42H0z"/>
        <path fill="#7FBA00" d="M46 0h42v42H46z"/>
        <path fill="#00A4EF" d="M0 46h42v42H0z"/>
        <path fill="#FFB900" d="M46 46h42v42H46z"/>
      </svg>
      <h3>Download for Windows</h3>
      <?php else: /* mac-arm64 or mac-x64 */ ?>
      <svg class="os-logo" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.08zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
      </svg>
      <h3>Download for <?= h($p['label']) ?></h3>
      <?php endif; ?>
      <p class="os-spec"><?= h($p['spec']) ?></p>

      <?php if ($p['available']): ?>
      <div class="file-list">
        <?php foreach ($p['files'] as $fi): ?>
        <div class="file-entry">
          <div class="file-entry-head">
            <span class="arch-badge arch-<?= $fi['arch']['badge'] ?>"><?= h($fi['arch']['label']) ?></span>
            <?php if ($fi['version']): ?>
            <span class="file-version">v<?= h($fi['version']) ?></span>
            <?php endif; ?>
          </div>
          <div class="file-info">
            <div class="file-info-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
              </svg>
            </div>
            <div class="file-info-text">
              <div class="file-info-name"><?= h($fi['filename']) ?></div>
              <div class="file-info-meta"><?= h($fi['size_human'] ?: 'Available') ?></div>
            </div>
          </div>
          <a href="<?= h($fi['filename']) ?>" class="btn-download" download>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Download <?= h($p['ext_label']) ?>
          </a>
        </div>
        <?php endforeach; ?>
      </div>

      <?php if ($p['troubleshooting']): ?>
      <div class="troubleshoot">
        <h4><?= h($p['troubleshooting']['title']) ?></h4>
        <ol>
          <?php foreach ($p['troubleshooting']['steps'] as $step): ?>
          <li><?= $step /* contains safe HTML */ ?></li>
          <?php endforeach; ?>
        </ol>
        <?php if (!empty($p['troubleshooting']['fix_title'])): ?>
        <div class="troubleshoot-fix">
          <strong><?= h($p['troubleshooting']['fix_title']) ?></strong>
          <p><?= h($p['troubleshooting']['fix_desc']) ?></p>
          <code><?= h($p['troubleshooting']['fix_cmd']) ?></code>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php else: ?>
      <span class="btn-download disabled">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <polyline points="12 6 12 12 16 14"/>
        </svg>
        Coming Soon
      </span>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>

  </div>

  <div class="trust-strip">
    <div class="trust-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
      </svg>
      <div class="trust-item-label">Secure Login</div>
      <div class="trust-item-meta">Device licensed</div>
    </div>
    <div class="trust-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
        <polyline points="23 4 23 10 17 10"/>
      </svg>
      <div class="trust-item-label">Auto Updates</div>
      <div class="trust-item-meta">Always current</div>
    </div>
    <div class="trust-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
        <polyline points="22 4 12 14.01 9 11.01"/>
      </svg>
      <div class="trust-item-label">Offline Charts</div>
      <div class="trust-item-meta">Swiss Ephemeris</div>
    </div>
    <div class="trust-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="16" x2="12" y2="12"/>
        <line x1="12" y1="8" x2="12.01" y2="8"/>
      </svg>
      <div class="trust-item-label">Version <?= h($displayVersion) ?></div>
      <div class="trust-item-meta">Released <?= h($displayDate) ?></div>
    </div>
  </div>
</section>

<section class="section" id="features">
  <div class="section-header">
    <div class="section-eyebrow">Twenty modules · One application</div>
    <h2>Everything a <em>Jyotishi</em> needs</h2>
    <p class="section-sub">From daily Panchang to detailed Kundali analysis — a complete toolkit grounded in classical Vedic tradition.</p>
  </div>

  <div class="features-grid">
    <div class="feature"><div class="feature-hi">गोचर</div><div class="feature-en">Gochar</div><div class="feature-desc">Real-time planetary transits with precise nakshatra positions.</div></div>
    <div class="feature"><div class="feature-hi">कुंडली</div><div class="feature-en">Kundali</div><div class="feature-desc">Detailed birth charts with all divisional vargas.</div></div>
    <div class="feature"><div class="feature-hi">प्रश्न</div><div class="feature-en">Prashna</div><div class="feature-desc">Horary astrology for questions arising in the moment.</div></div>
    <div class="feature"><div class="feature-hi">पंचांग</div><div class="feature-en">Panchang</div><div class="feature-desc">Daily Hindu calendar with tithi, yoga, karana, and nakshatra.</div></div>
    <div class="feature"><div class="feature-hi">विवाह मिलान</div><div class="feature-en">Vivah Milan</div><div class="feature-desc">Ashtakoot guna milan with detailed compatibility analysis.</div></div>
    <div class="feature"><div class="feature-hi">मुहूर्त</div><div class="feature-en">Muhurta</div><div class="feature-desc">Find auspicious timings for any event or ceremony.</div></div>
    <div class="feature"><div class="feature-hi">दशा</div><div class="feature-en">Dasha</div><div class="feature-desc">Vimshottari and other planetary period systems.</div></div>
    <div class="feature"><div class="feature-hi">योग</div><div class="feature-en">Yoga</div><div class="feature-desc">Identification of raja, dhana, and other classical yogas.</div></div>
  </div>
</section>

<section class="section" id="mobile-app">
  <div class="section-header">
    <div class="section-eyebrow">Android app · Available now</div>
    <h2>Divya Jyotish <em>on the go</em></h2>
    <p class="section-sub">The complete Vedic astrology toolkit for astrologers, students, and enthusiasts — now in your pocket.</p>
  </div>

  <div class="app-content">
    <div class="phone-wrap">
      <div class="phone">
        <div class="phone-screen">
          <span class="phone-notch"></span>
          <span class="ph-star"></span>
          <span class="ph-star"></span>
          <span class="ph-star"></span>
          <span class="ph-star"></span>
          <span class="ph-star"></span>
          <span class="phone-om">ॐ</span>
          <span class="phone-line"></span>
          <span class="phone-label">Divya Jyotish</span>
          <span class="phone-indicator"></span>
        </div>
      </div>
    </div>

    <div>
      <p class="app-desc">
        From Janma Kundali generation to precise Gochar transit predictions — the same authentic Vedic astrology engine that powers our desktop application, reimagined for mobile.
      </p>

      <div class="app-feats">
        <div class="app-feat">
          <span class="app-feat-chk"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
          Janma Kundali Generation
        </div>
        <div class="app-feat">
          <span class="app-feat-chk"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
          Gochar Transit Predictions
        </div>
        <div class="app-feat">
          <span class="app-feat-chk"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
          Prashna Horary Analysis
        </div>
        <div class="app-feat">
          <span class="app-feat-chk"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
          Ashtakoota Marriage Matching
        </div>
        <div class="app-feat">
          <span class="app-feat-chk"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
          Daily Panchang &amp; Vikram Samvat
        </div>
        <div class="app-feat">
          <span class="app-feat-chk"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
          Numerology &amp; Vastu Analysis
        </div>
        <div class="app-feat">
          <span class="app-feat-chk"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
          Planetary Yogas &amp; Aspects
        </div>
        <div class="app-feat">
          <span class="app-feat-chk"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
          Sankranti &amp; Samvatsara
        </div>
      </div>

      <a href="https://play.google.com/store/apps/details?id=com.divyajyotish.cb" class="play-btn" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M2.5 2.5v19l8.5-9.5Zm1.7 1.7 7.5 8.3-7.5 8.3 14-8.3ZM21 12l-3.3-1.9-2.1 1.9 2.1 1.9ZM5.7 4.2l11.3 6.8 2.8-1.6Z"/></svg>
        Get it on Google Play
      </a>
    </div>
  </div>
</section>

<footer>
  <div class="footer-inner">
    <div class="footer-grid">
      <div>
        <a href="#" class="brand">
          <span class="brand-mark">ॐ</span>
          <span class="brand-text">
            <span class="brand-text-en" style="color: var(--cream);"><?= h($APP_NAME) ?></span>
            <span class="brand-text-hi" style="color: var(--gold);"><?= h($APP_NAME_HI) ?></span>
          </span>
        </a>
        <p class="footer-brand-text">
          Professional Vedic astrology software for desktop. Built with reverence for tradition and respect for craft.
        </p>
      </div>
      <div class="footer-col">
        <h4>Product</h4>
        <ul>
          <li><a href="#download">Download</a></li>
          <li><a href="#mobile-app">Mobile App</a></li>
          <li><a href="#features">Modules</a></li>
          <li><a href="/changelog">Changelog</a></li>
          <li><a href="/login">Login</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Support</h4>
        <ul>
          <li><a href="mailto:<?= h($SUPPORT_EMAIL) ?>">Email</a></li>
          <li><a href="#">WhatsApp</a></li>
          <li><a href="/docs">Documentation</a></li>
          <li><a href="/faq">FAQ</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Legal</h4>
        <ul>
          <li><a href="/privacy">Privacy</a></li>
          <li><a href="/terms">Terms</a></li>
          <li><a href="/license">License</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= h($BRAND_YEAR) ?> <?= h($APP_NAME) ?> · Made with reverence in Nepal 🇳🇵</span>
      <span class="sanskrit-blessing">॥ ज्योतिषं ज्ञानं दिव्यम् ॥</span>
    </div>
  </div>
</footer>

<script>
// Sticky nav scroll effect — the only JS needed since everything else is server-rendered
(function() {
  const nav = document.getElementById('topnav');
  function update() {
    if (window.scrollY > 8) nav.classList.add('scrolled');
    else nav.classList.remove('scrolled');
  }
  window.addEventListener('scroll', update, { passive: true });
  update();
})();
</script>

</body>
</html>
