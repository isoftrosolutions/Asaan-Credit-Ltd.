<?php
$dir = __DIR__ . '/public/uploads/business-documents';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
    echo "Created $dir\n";
} else {
    echo "$dir already exists\n";
}
$teamDir = __DIR__ . '/public/uploads/team';
if (!is_dir($teamDir)) {
    mkdir($teamDir, 0755, true);
    echo "Created $teamDir\n";
}

$resumeDir = __DIR__ . '/public/uploads/resumes';
if (!is_dir($resumeDir)) {
    mkdir($resumeDir, 0755, true);
    echo "Created $resumeDir\n";
}

$ref = __DIR__ . '/public/uploads/business-photos';
$perms = is_dir($ref) ? fileperms($ref) & 0777 : 0755;
chmod($dir, $perms);
echo "Permissions set to " . decoct($perms) . "\n";
