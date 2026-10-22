<?php
/**
 * Builds the signed Nexus clinic booking URL and redirects the browser to it.
 *
 * The token is signed here so the Nexus shared secret never reaches the
 * browser. Called by openClinicLink() in vaccine_index.php and
 * vaccine_campaign.php through a POST form opened in a new tab.
 *
 * The header format must stay identical to what Nexus expects:
 * base64(JSON) where Token = sha256(name|ic|phone|receipt|channel|comments|exp + secret)
 * and exp is the current 30-second window.
 */
require_once __DIR__ . '/vaccine_env.php';

session_start();

// Same login rule as lock_adv.php, without its HTML output so the redirect header can be sent.
if (!isset($_SESSION['myusername']) || ($_SESSION['type'] ?? '') !== 'odb') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Session expired. Please log in to ODB again.';
    exit;
}

header('Content-Type: text/plain; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed.';
    exit;
}

$secret = vaccine_env('NEXUS_SECRET');
if ($secret === '') {
    http_response_code(500);
    error_log('vaccine_clinic_link: NEXUS_SECRET is missing from vaccination/.env');
    echo 'Clinic booking is not configured. Please contact the system administrator.';
    exit;
}

$ic = (string) ($_POST['ic'] ?? '');
$name = (string) ($_POST['name'] ?? '');
$phone = (string) ($_POST['phone'] ?? '');
$receiptNo = trim((string) ($_POST['receipt_no'] ?? ''));
$paymentChannel = trim((string) ($_POST['payment_channel'] ?? ''));

$errors = [];
if (trim($ic) === '') {
    $errors[] = 'NRIC is missing';
}
if (trim($name) === '') {
    $errors[] = 'Customer name is missing';
}
if (trim($phone) === '') {
    $errors[] = 'Mobile number is missing';
}
if ($errors !== []) {
    http_response_code(422);
    echo "Please complete the following before proceeding:\n- " . implode("\n- ", $errors);
    exit;
}

$exp = intdiv(time(), 30);
$comments = 'Referral Case from Octopus Module';
$payload = implode('|', [$name, $ic, $phone, $receiptNo, $paymentChannel, $comments, $exp]);

$headerData = [
    'NRIC' => $ic,
    'PatientName' => $name,
    'MobileNo' => $phone,
    'ReceiptNo' => $receiptNo,
    'PaymentChannel' => $paymentChannel,
    'Comments' => $comments,
    'Exp' => $exp,
    'Token' => hash('sha256', $payload . $secret),
];

// Flags make the output byte-identical to the browser's JSON.stringify used previously.
$headerJson = json_encode(
    $headerData,
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS
);
if ($headerJson === false) {
    http_response_code(422);
    echo 'Customer details contain invalid characters.';
    exit;
}

header(
    'Location: http://thenexushealth.com/OctopusBookPublicAppointment?header='
    . rawurlencode(base64_encode($headerJson)),
    true,
    303
);
exit;
