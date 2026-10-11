<?php
declare(strict_types=1);

/*
 * PoptejPlot.cz form endpoint.
 * Deploy on PHP hosting (for example WEDOS), not GitHub Pages.
 * Keep api/config.php out of the public Git repository.
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function respond(int $status, bool $ok, string $message): never {
    http_response_code($status);
    echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST, OPTIONS');
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') { http_response_code(204); exit; }
    respond(405, false, 'Tento způsob odeslání není podporován.');
}

$allowedOrigins = ['https://poptejplot.cz', 'https://www.poptejplot.cz'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (!in_array($origin, $allowedOrigins, true)) {
    respond(403, false, 'Odeslání z této adresy není povoleno.');
}
header('Access-Control-Allow-Origin: ' . $origin);
header('Vary: Origin');

$contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
if ($contentType !== 'application/json') {
    respond(415, false, 'Formulář odešlete znovu.');
}

$raw = file_get_contents('php://input');
if ($raw === false || strlen($raw) > 16000) {
    respond(413, false, 'Formulář je příliš dlouhý.');
}
$data = json_decode($raw, true);
if (!is_array($data)) {
    respond(400, false, 'Odeslaná data se nepodařilo načíst.');
}
if (!empty($data['website'])) {
    respond(200, true, 'Děkujeme.');
}
$startedAt = filter_var($data['started_at'] ?? null, FILTER_VALIDATE_INT);
if (!$startedAt || (int) floor(microtime(true) * 1000) - $startedAt < 1200) {
    respond(400, false, 'Zkuste formulář odeslat ještě jednou.');
}
if (($data['consent'] ?? '') !== 'yes') {
    respond(400, false, 'Pro odeslání je potřeba potvrdit informace o zpracování údajů.');
}

$configPath = __DIR__ . '/config.php';
if (!is_file($configPath)) {
    respond(503, false, 'Odesílání ještě není na hostingu nastaveno. Napište nám prosím e-mail.');
}
$config = require $configPath;
if (!is_array($config) || empty($config['operator']) || empty($config['from_email'])) {
    respond(503, false, 'Odesílání ještě není na hostingu nastaveno. Napište nám prosím e-mail.');
}
$operator = trim((string)$config['operator']);
if (stripos($operator, 'DOPLNIT') !== false) {
    respond(503, false, 'Odesílání čeká na dokončení údajů provozovatele.');
}

function cleanText(mixed $value, int $max): string {
    if (!is_scalar($value)) return '';
    $text = trim((string)$value);
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? '';
    return mb_substr($text, 0, $max, 'UTF-8');
}
function safeList(mixed $value, int $maxCount = 20): array {
    if (!is_array($value)) return [];
    $items = [];
    foreach (array_slice($value, 0, $maxCount) as $item) {
        $clean = cleanText($item, 100);
        if ($clean !== '') $items[] = $clean;
    }
    return $items;
}
function addLine(array &$lines, string $label, string $value): void {
    if ($value !== '') $lines[] = $label . ': ' . $value;
}

$type = cleanText($data['form_type'] ?? '', 20);
$name = cleanText($data['name'] ?? '', 120);
$email = cleanText($data['email'] ?? '', 190);
$phone = cleanText($data['phone'] ?? '', 30);
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '') {
    respond(422, false, 'Zkontrolujte jméno, e-mail a telefon.');
}

if ($type === 'lead') {
    $fenceType = cleanText($data['type'] ?? '', 80);
    $length = filter_var($data['length'] ?? null, FILTER_VALIDATE_FLOAT);
    $place = cleanText($data['place'] ?? '', 100);
    $height = cleanText($data['height'] ?? '', 10);
    $description = cleanText($data['description'] ?? '', 3000);
    $extras = safeList($data['extras'] ?? []);
    if ($fenceType === '' || $length === false || $length < 1 || $length > 1000 || $place === '') {
        respond(422, false, 'Vyplňte typ plotu, délku a obec realizace.');
    }
    $recipient = 'info@poptejplot.cz';
    $subject = 'Nová poptávka na oplocení';
    $lines = ['Nová poptávka z PoptejPlot.cz', '', 'Provozovatel: ' . $operator];
    addLine($lines, 'Typ plotu', $fenceType);
    addLine($lines, 'Délka (m)', (string)$length);
    addLine($lines, 'Výška (cm)', $height);
    addLine($lines, 'Obec', $place);
    addLine($lines, 'Příslušenství', implode(', ', $extras));
    addLine($lines, 'Další informace', $description);
    addLine($lines, 'Jméno', $name);
    addLine($lines, 'E-mail', $email);
    addLine($lines, 'Telefon', $phone);
    $success = 'Poptávku jsme přijali k dalšímu zpracování.';
} elseif ($type === 'partner') {
    $company = cleanText($data['company'] ?? '', 160);
    $ico = preg_replace('/\D+/', '', cleanText($data['ico'] ?? '', 20)) ?? '';
    $regions = cleanText($data['regions'] ?? '', 500);
    $services = safeList($data['services'] ?? []);
    $fences = safeList($data['fences'] ?? []);
    $websiteUrl = cleanText($data['website_url'] ?? '', 250);
    $description = cleanText($data['description'] ?? '', 2000);
    if ($company === '' || $regions === '' || count($services) === 0 || count($fences) === 0) {
        respond(422, false, 'Doplňte firmu, region, služby a typy oplocení.');
    }
    if ($ico !== '' && !preg_match('/^\d{8}$/', $ico)) {
        respond(422, false, 'IČO musí obsahovat osm číslic.');
    }
    if ($websiteUrl !== '' && !filter_var($websiteUrl, FILTER_VALIDATE_URL)) {
        respond(422, false, 'Zkontrolujte webovou adresu firmy.');
    }
    $recipient = 'partners@poptejplot.cz';
    $subject = 'Registrace firmy – PoptejPlot.cz';
    $lines = ['Nová registrace firmy z PoptejPlot.cz', '', 'Provozovatel: ' . $operator];
    addLine($lines, 'Firma', $company);
    addLine($lines, 'IČO', $ico);
    addLine($lines, 'Kontaktní osoba', $name);
    addLine($lines, 'E-mail', $email);
    addLine($lines, 'Telefon', $phone);
    addLine($lines, 'Web', $websiteUrl);
    addLine($lines, 'Služby', implode(', ', $services));
    addLine($lines, 'Typy oplocení', implode(', ', $fences));
    addLine($lines, 'Regiony', $regions);
    addLine($lines, 'Další informace', $description);
    $success = 'Registraci jsme přijali k dalšímu zpracování.';
} else {
    respond(400, false, 'Neznámý typ formuláře.');
}

$from = filter_var((string)$config['from_email'], FILTER_VALIDATE_EMAIL);
if (!$from || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
    respond(503, false, 'E-mailová schránka formuláře není správně nastavena.');
}
$replyTo = filter_var($email, FILTER_VALIDATE_EMAIL);
$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'From: PoptejPlot.cz <' . $from . '>',
];
if ($replyTo) $headers[] = 'Reply-To: ' . $replyTo;
$message = implode("\r\n", $lines) . "\r\n";
$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
if (!@mail($recipient, $encodedSubject, $message, implode("\r\n", $headers))) {
    respond(502, false, 'E-mail se nepodařilo předat poštovnímu serveru. Zkuste to prosím později.');
}
respond(200, true, $success);
