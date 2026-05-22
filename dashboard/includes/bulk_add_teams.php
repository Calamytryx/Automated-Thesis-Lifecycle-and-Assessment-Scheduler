<?php
// CRITICAL: Start session FIRST before accessing $_SESSION
session_start();

require_once __DIR__ . '/../../assets/setup/db.inc.php';
require_once __DIR__ . '/section_access.php';

// Get current user info for section-based permission checks
$userId = $_SESSION['id'] ?? 0;
$usertype = $_SESSION['usertype'] ?? -1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // NEW: Determine upload method similar to bulk add users
    $uploadMethod = '';
    if (!empty($_FILES['bulkTeamsFile']['tmp_name'])) {
        $uploadMethod = 'file';
    } elseif (!empty($_POST['bulkTeamsTextInput'])) {
        $uploadMethod = 'paste';
    } elseif (!empty($_POST['teams'])) {
        $uploadMethod = 'form';
    }

    $teams = [];

    if ($uploadMethod === 'paste') {
        // If bulk text is provided, assume CSV with columns: Team Name, Research Title, Area of Expertise, Program, [Members]
        $lines = explode("\n", trim($_POST['bulkTeamsTextInput']));
        foreach ($lines as $line) {
            // Basic CSV parsing (adjust delimiter if needed)
            $parts = str_getcsv($line);
            if (count($parts) >= 4) {
                $teamEntry = [
                    'name' => trim($parts[0]),
                    'title' => trim($parts[1]),
                    'area_of_expertise' => trim($parts[2]),
                    'program' => trim($parts[3])
                ];
                // NEW: Capture members info if provided (expected format: username:role;username:role)
                if (count($parts) >= 5 && trim($parts[4]) !== '') {
                    $teamEntry['members'] = trim($parts[4]);
                }
                $teams[] = $teamEntry;
            }
        }
    } elseif ($uploadMethod === 'form' && isset($_POST['teams']) && is_array($_POST['teams'])) {
        // Also, check if teams are provided via manual table input
        foreach ($_POST['teams'] as $team) {
            if (!empty($team['name']) && !empty($team['title'])) {
                $entry = [
                    'name' => $team['name'],
                    'title' => $team['title'],
                    'area_of_expertise' => $team['area_of_expertise'] ?? null,
                    'program' => $team['program'] ?? null
                ];
                if (isset($team['members'])) {
                    $entry['members'] = $team['members']; // same expected format
                }
                $teams[] = $entry;
            }
        }
    }
    // Optionally, you may later add file processing for $uploadMethod === 'file'

    if (empty($teams)) {
        echo json_encode(['success' => false, 'message' => 'No valid team data provided']);
        exit;
    }

    $pdo->beginTransaction();
    try {
        foreach ($teams as $team) {
            // 🔐 Section-based permission check for professors (usertype 2)
            if ($usertype == 2 && $userId != 0) {
                // Get all member IDs for this team to check their sections
                $memberIds = [];
                if (!empty($team['members'])) {
                    $membersStr = $team['members'];
                    $membersList = array_filter(array_map('trim', explode(';', $membersStr)));
                    foreach ($membersList as $memberEntry) {
                        list($username, $role) = array_map('trim', explode(':', $memberEntry, 2));
                        if ($username) {
                            $stmtLookup = $pdo->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
                            $stmtLookup->execute(['username' => $username]);
                            $result = $stmtLookup->fetch(PDO::FETCH_ASSOC);
                            if ($result) {
                                $memberIds[] = $result['id'];
                            }
                        }
                    }
                }
                
                // Check permission
                $permCheck = canProfessorCreateTeam($pdo, $userId, $memberIds);
                if (!$permCheck['canCreate']) {
                    throw new Exception($permCheck['message']);
                }
            }
            
            // Insert into teams table
            $stmt = $pdo->prepare("INSERT INTO teams (name, area_of_expertise, program) VALUES (:name, :area_of_expertise, :program)");
            $stmt->execute([
                'name' => $team['name'],
                'area_of_expertise' => $team['area_of_expertise'],
                'program' => $team['program']
            ]);
            // Get inserted team id
            $teamId = $pdo->lastInsertId();
            
            // Insert research title for the team (do not supply id)
            $stmt = $pdo->prepare("INSERT INTO research_titles (team_id, title) VALUES (:team_id, :title)");
            $stmt->execute([
                'team_id' => $teamId,
                'title' => $team['title']
            ]);
            
            // 🎓 TITLE PROPOSAL AUTO-ASSIGNMENT: If professor bulk creates a title proposal team, auto-assign them as adviser
            if ($usertype == 2 && isTitleProposal($team['title'])) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (:team_id, :user_id, :role)");
                    $stmt->execute([
                        'team_id' => $teamId,
                        'user_id' => $userId,
                        'role' => 'adviser'
                    ]);
                } catch (PDOException $adviserError) {
                    error_log("Note: Could not auto-assign professor as adviser in bulk add: " . $adviserError->getMessage());
                }
            }
            
            // NEW: If team members are provided, process them; otherwise, insert a default leader
            if (!empty($team['members'])) {
                // Expected format: "username:role;username:role"
                $membersStr = $team['members'];
                $membersList = array_filter(array_map('trim', explode(';', $membersStr)));
                foreach ($membersList as $memberEntry) {
                    list($username, $role) = array_map('trim', explode(':', $memberEntry, 2));
                    if ($username && $role) {
                        $stmtLookup = $pdo->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
                        $stmtLookup->execute(['username' => $username]);
                        $result = $stmtLookup->fetch(PDO::FETCH_ASSOC);
                        if ($result) {
                            $stmtMember = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (:team_id, :user_id, :role)");
                            $stmtMember->execute([
                                'team_id' => $teamId,
                                'user_id' => $result['id'],
                                'role' => $role
                            ]);
                        }
                    }
                }
            } else {
                // Insert default team member as leader if no members info provided
                $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (:team_id, NULL, :role)");
                $stmt->execute([
                    'team_id' => $teamId,
                    'role' => 'leader'
                ]);
            }
        }
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
