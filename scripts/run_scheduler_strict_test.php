<?php
// One-off runner to invoke the scheduler in strict mode for diagnostics
chdir(__DIR__ . '/..');
$_SERVER['REQUEST_METHOD'] = 'POST';
session_start();
// Run as admin
$_SESSION['id'] = 0;
$_SESSION['usertype'] = 0;
// Inputs (from user)
$_POST['rooms'] = ['Defense Room 1', 'Defense Room 2', 'Accreditation Room'];
$_POST['timeDuration'] = '2.00';
// Provide explicit timeSlots (30-minute increments) so server conversion isn't needed
// For 2h duration, last start must be 18:30 to end by 20:30
$_POST['timeSlots'] = [];
$t = strtotime('07:00');
$endCap = strtotime('18:30');
while ($t <= $endCap) {
    $_POST['timeSlots'][] = date('H:i', $t);
    $t += 30 * 60; // 30 minutes
}
$_POST['days'] = [
    '05-28-2026','05-29-2026','05-30-2026','05-27-2026','05-25-2026','05-26-2026','05-23-2026','05-22-2026','05-21-2026',
    '06-01-2026','06-02-2026','06-03-2026','06-04-2026','06-05-2026','06-06-2026','06-08-2026','06-09-2026','06-10-2026','06-11-2026','06-12-2026','06-13-2026'
];
$_POST['includeLunchBreak'] = 'true';
$_POST['validationMode'] = 'strict';
// minimal required form fields
$_POST['progressId'] = 'test_strict_run_' . uniqid();
// Restrict to the specific 11 teams from the preview to match the user's run
$_POST['unresolved_team_ids'] = implode(',', [14,15,20,13,19,21,17,18,8,22,16]);
// call the scheduler
require_once __DIR__ . '/../dashboard/includes/run_scheduler.php';
