<?php
require_once '../assets/setup/db.inc.php';

echo "<h2>Generating Faculty Schedules</h2>";

// First, get all faculty members (usertype = 2)
$stmt = $pdo->query("
    SELECT id, CONCAT(first_name, ' ', last_name) as name, program, area_of_expertise, is_parttime 
    FROM users 
    WHERE usertype = 2 
    ORDER BY program, last_name, first_name
");
$faculty = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Faculty Members:</h3>";
foreach ($faculty as $prof) {
    echo "ID: {$prof['id']} - {$prof['name']} - Program: {$prof['program']} - Expertise: {$prof['area_of_expertise']} - Part-time: " . ($prof['is_parttime'] ? 'Yes' : 'No') . "<br>";
}

// Get program IDs
$stmt = $pdo->query("SELECT id, name, specialization FROM programs WHERE id IN (78, 80, 86)");
$programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Programs:</h3>";
foreach ($programs as $prog) {
    echo "ID: {$prog['id']} - {$prog['name']} - {$prog['specialization']}<br>";
}

// Define sections - these should be close to MINIMUM (24 hours)
$sections = ['IT401', 'IT402', 'IT403', 'CS401'];

// Fill sections - these should be close to MAXIMUM (30 hours) to increase faculty load
$fillSections = ['CpE401', 'CpE402', 'CpE403', 'CS402', 'CS403', 'CS301', 'CS302', 'CS303', 'IT301', 'IT302', 'IT303', 'CS201', 'CS202', 'CS203', 'IT201', 'IT202', 'IT203'];

// Define class types with their durations in hours
$classTypes = [
    'lecture' => ['duration' => 2, 'description' => '2-hour lecture'],
    'extended' => ['duration' => 3, 'description' => '3-hour class'],
    'lab' => ['duration' => 3, 'description' => '3-hour laboratory']
];

// Define rooms
$rooms = ['L101', 'L102', 'L103', 'L201', 'L202', 'L203', 'C601', 'C602', 'C603', 'C609'];

// Define days
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Define working hours (in minutes from midnight)
$dayStart = 7 * 60;  // 7:00 AM = 420 minutes
$dayEnd = 21 * 60;   // 9:00 PM = 1260 minutes
$breakTimes = [
    ['start' => 12 * 60, 'end' => 13 * 60], // Lunch break 12:00-13:00
];

// Section hour limits
$primarySectionMinHours = 24;
$primarySectionMaxHours = 26; // Close to minimum
$fillSectionMinHours = 27;
$fillSectionMaxHours = 30; // Close to maximum

// Function to convert minutes to time string
function minutesToTime($minutes) {
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return sprintf('%02d:%02d:00', $hours, $mins);
}

// Function to check if a time range overlaps with breaks
function overlapsBreak($start, $end, $breakTimes) {
    foreach ($breakTimes as $break) {
        if ($start < $break['end'] && $end > $break['start']) {
            return true;
        }
    }
    return false;
}

// Function to check if two time ranges overlap
function timesOverlap($start1, $end1, $start2, $end2) {
    return $start1 < $end2 && $end1 > $start2;
}

// Global used slots tracker for all conflicts
$globalUsedSlots = [];

// Function to find available slot for a given duration
function findAvailableSlot($day, $durationHours, &$globalUsedSlots, $dayStart, $dayEnd, $breakTimes, $section, $facultyId, $room) {
    $durationMinutes = $durationHours * 60;
    $attempts = 0;
    $maxAttempts = 100;
    
    while ($attempts < $maxAttempts) {
        // Generate random start time (aligned to 30-minute intervals)
        $possibleStart = $dayStart + (rand(0, (int)(($dayEnd - $dayStart - $durationMinutes) / 30)) * 30);
        $possibleEnd = $possibleStart + $durationMinutes;
        
        // Check if within working hours
        if ($possibleEnd > $dayEnd) {
            $attempts++;
            continue;
        }
        
        // Check if overlaps with break
        if (overlapsBreak($possibleStart, $possibleEnd, $breakTimes)) {
            $attempts++;
            continue;
        }
        
        // Check for conflicts with existing slots
        $hasConflict = false;
        
        foreach ($globalUsedSlots as $slotData) {
            if ($slotData['day'] !== $day) {
                continue;
            }
            
            if (!timesOverlap($possibleStart, $possibleEnd, $slotData['start'], $slotData['end'])) {
                continue;
            }
            
            // Check section conflicts (same section, same time)
            if ($slotData['section'] === $section) {
                $hasConflict = true;
                break;
            }
            
            // Check faculty conflicts (same faculty, same time)
            if ($slotData['faculty_id'] === $facultyId) {
                $hasConflict = true;
                break;
            }
            
            // Check room conflicts (same room, same time)
            if ($slotData['room'] === $room) {
                $hasConflict = true;
                break;
            }
        }
        
        if (!$hasConflict) {
            return ['start' => $possibleStart, 'end' => $possibleEnd];
        }
        
        $attempts++;
    }
    
    return null;
}

// IT courses
$itCourses = [
    'Web Development',
    'Mobile Development',
    'Database Systems',
    'Systems Development',
    'Cybersecurity',
    'Networking',
    'UI/UX Design',
    'DevOps',
    'Project Management',
    'Cloud Computing',
    'Software Testing',
    'IT Capstone',
    'Web Technologies',
    'Enterprise Systems'
];

// CS courses
$csCourses = [
    'Machine Learning',
    'Algorithms',
    'AI Research',
    'Data Science',
    'Software Design',
    'Computer Vision',
    'Natural Language Processing',
    'Deep Learning',
    'Theory of Computation',
    'Compiler Design',
    'Operating Systems',
    'CS Thesis'
];

// CpE courses (for filling hours)
$cpeCourses = [
    'Embedded Systems',
    'Digital Signal Processing',
    'Computer Architecture',
    'VLSI Design',
    'Control Systems',
    'Microprocessors',
    'Computer Networks',
    'IoT Systems',
    'Robotics',
    'Hardware Design'
];

// Clear existing schedules
$pdo->exec("DELETE FROM user_schedules");
echo "<h3>Cleared existing schedules</h3>";

// Track faculty hours and section hours
$facultyHours = [];
$sectionHours = [];

foreach ($faculty as $prof) {
    $facultyHours[$prof['id']] = 0;
}

foreach ($sections as $section) {
    $sectionHours[$section] = 0;
}

foreach ($fillSections as $section) {
    $sectionHours[$section] = 0;
}

// Generate schedules
$insertedSchedules = [];

// Phase 1: Assign classes to primary sections - target close to MINIMUM (24-26 hours)
echo "<h3>Phase 1: Primary sections (target 24-26 hours - close to minimum)</h3>";

foreach ($sections as $section) {
    $isIT = strpos($section, 'IT') === 0;
    $program = $isIT ? 80 : 78;
    $courses = $isIT ? $itCourses : $csCourses;
    $year = (int)substr($section, -3, 1);
    
    // Get faculty members for this program
    $eligibleFaculty = array_filter($faculty, function($f) use ($program) {
        $profProgram = $f['program'];
        if ($program == 80) {
            return stripos($profProgram, 'Information Technology') !== false;
        } else {
            return stripos($profProgram, 'Computer Science') !== false;
        }
    });
    
    if (empty($eligibleFaculty)) {
        echo "<p style='color:red;'>Warning: No faculty found for section $section (program $program)</p>";
        continue;
    }
    
    $classTypeKeys = array_keys($classTypes);
    $attempts = 0;
    $maxTotalAttempts = 500;
    
    // Target close to minimum (24-26 hours)
    while ($sectionHours[$section] < $primarySectionMinHours && $attempts < $maxTotalAttempts) {
        $attempts++;
        
        // Calculate remaining hours needed
        $remainingHours = $primarySectionMaxHours - $sectionHours[$section];
        
        // Pick appropriate class type based on remaining hours
        $validTypes = array_filter($classTypes, function($ct) use ($remainingHours) {
            return $ct['duration'] <= $remainingHours;
        });
        
        if (empty($validTypes)) {
            break;
        }
        
        $classTypeKey = array_rand($validTypes);
        $classType = $validTypes[$classTypeKey];
        $duration = $classType['duration'];
        
        // Pick a faculty member who hasn't exceeded their hours
        $availableFaculty = array_filter($eligibleFaculty, function($f) use ($facultyHours, $duration) {
            $maxHours = $f['is_parttime'] ? 12 : 40;
            return $facultyHours[$f['id']] + $duration <= $maxHours;
        });
        
        if (empty($availableFaculty)) {
            $availableFaculty = $eligibleFaculty;
        }
        
        if (empty($availableFaculty)) {
            break;
        }
        
        $selectedFaculty = $availableFaculty[array_rand($availableFaculty)];
        
        // Pick a random room
        $room = $rooms[array_rand($rooms)];
        
        // Try to find a slot on any day
        $slot = null;
        $selectedDay = null;
        shuffle($days);
        
        foreach ($days as $day) {
            $slot = findAvailableSlot($day, $duration, $globalUsedSlots, $dayStart, $dayEnd, $breakTimes, $section, $selectedFaculty['id'], $room);
            if ($slot !== null) {
                $selectedDay = $day;
                break;
            }
            foreach ($rooms as $tryRoom) {
                $slot = findAvailableSlot($day, $duration, $globalUsedSlots, $dayStart, $dayEnd, $breakTimes, $section, $selectedFaculty['id'], $tryRoom);
                if ($slot !== null) {
                    $selectedDay = $day;
                    $room = $tryRoom;
                    break 2;
                }
            }
        }
        
        if ($slot === null) {
            continue;
        }
        
        // Record the slot globally
        $globalUsedSlots[] = [
            'day' => $selectedDay,
            'start' => $slot['start'],
            'end' => $slot['end'],
            'section' => $section,
            'faculty_id' => $selectedFaculty['id'],
            'room' => $room
        ];
        
        // Pick a random course
        $course = $courses[array_rand($courses)];
        
        // Convert minutes to time strings
        $startTime = minutesToTime($slot['start']);
        $endTime = minutesToTime($slot['end']);
        
        // Add to schedules
        $insertedSchedules[] = [
            'user_id' => $selectedFaculty['id'],
            'program' => $program,
            'section' => $section,
            'room' => $room,
            'day_of_week' => $selectedDay,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'class_name' => $course . " ({$classType['description']})",
            'year' => $year
        ];
        
        // Update tracking
        $facultyHours[$selectedFaculty['id']] += $duration;
        $sectionHours[$section] += $duration;
    }
    
    echo "Section $section: {$sectionHours[$section]} hours assigned<br>";
}

// Phase 2: Fill sections to close to MAXIMUM (27-30 hours) to increase faculty load
echo "<h3>Phase 2: Fill sections (target 27-30 hours - close to maximum)</h3>";

foreach ($fillSections as $section) {
    $year = (int)substr($section, -3, 1);
    
    // Determine program and courses based on section name
    if (strpos($section, 'IT') === 0) {
        $program = 80;
        $courses = $itCourses;
    } elseif (strpos($section, 'CS') === 0) {
        $program = 78;
        $courses = $csCourses;
    } else {
        $program = 86;
        $courses = $cpeCourses;
    }
    
    $attempts = 0;
    $maxTotalAttempts = 500;
    
    // Target close to maximum (27-30 hours)
    while ($sectionHours[$section] < $fillSectionMinHours && $attempts < $maxTotalAttempts) {
        $attempts++;
        
        $remainingHours = $fillSectionMaxHours - $sectionHours[$section];
        
        $validTypes = array_filter($classTypes, function($ct) use ($remainingHours) {
            return $ct['duration'] <= $remainingHours;
        });
        
        if (empty($validTypes)) {
            break;
        }
        
        $classTypeKey = array_rand($validTypes);
        $classType = $validTypes[$classTypeKey];
        $duration = $classType['duration'];
        
        // Pick a faculty member who needs more hours (prioritize those under target)
        $availableFaculty = array_filter($faculty, function($f) use ($facultyHours, $duration) {
            $maxHours = $f['is_parttime'] ? 12 : 40;
            $targetHours = $f['is_parttime'] ? 12 : 36;
            return $facultyHours[$f['id']] + $duration <= $maxHours && $facultyHours[$f['id']] < $targetHours;
        });
        
        if (empty($availableFaculty)) {
            // Try any faculty with room
            $availableFaculty = array_filter($faculty, function($f) use ($facultyHours, $duration) {
                $maxHours = $f['is_parttime'] ? 12 : 40;
                return $facultyHours[$f['id']] + $duration <= $maxHours;
            });
        }
        
        if (empty($availableFaculty)) {
            break;
        }
        
        // Sort by hours (ascending) to prioritize faculty with fewer hours
        usort($availableFaculty, function($a, $b) use ($facultyHours) {
            return $facultyHours[$a['id']] - $facultyHours[$b['id']];
        });
        
        // Pick from the faculty with fewer hours (first 3)
        $topCandidates = array_slice($availableFaculty, 0, min(3, count($availableFaculty)));
        $selectedFaculty = $topCandidates[array_rand($topCandidates)];
        
        $room = $rooms[array_rand($rooms)];
        
        $slot = null;
        $selectedDay = null;
        shuffle($days);
        
        foreach ($days as $day) {
            foreach ($rooms as $tryRoom) {
                $slot = findAvailableSlot($day, $duration, $globalUsedSlots, $dayStart, $dayEnd, $breakTimes, $section, $selectedFaculty['id'], $tryRoom);
                if ($slot !== null) {
                    $selectedDay = $day;
                    $room = $tryRoom;
                    break 2;
                }
            }
        }
        
        if ($slot === null) {
            continue;
        }
        
        $globalUsedSlots[] = [
            'day' => $selectedDay,
            'start' => $slot['start'],
            'end' => $slot['end'],
            'section' => $section,
            'faculty_id' => $selectedFaculty['id'],
            'room' => $room
        ];
        
        $course = $courses[array_rand($courses)];
        $startTime = minutesToTime($slot['start']);
        $endTime = minutesToTime($slot['end']);
        
        $insertedSchedules[] = [
            'user_id' => $selectedFaculty['id'],
            'program' => $program,
            'section' => $section,
            'room' => $room,
            'day_of_week' => $selectedDay,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'class_name' => $course . " ({$classType['description']})",
            'year' => $year
        ];
        
        $facultyHours[$selectedFaculty['id']] += $duration;
        $sectionHours[$section] += $duration;
    }
    
    echo "Section $section: {$sectionHours[$section]} hours assigned<br>";
}

// Phase 3: Additional pass to maximize faculty hours using fill sections
echo "<h3>Phase 3: Maximize faculty hours using fill sections</h3>";

foreach ($faculty as $prof) {
    $facultyId = $prof['id'];
    $maxFacultyHours = $prof['is_parttime'] ? 12 : 40;
    $targetFacultyHours = $prof['is_parttime'] ? 12 : 36;
    
    if ($facultyHours[$facultyId] >= $targetFacultyHours) {
        continue;
    }
    
    $attempts = 0;
    $maxTotalAttempts = 300;
    
    while ($facultyHours[$facultyId] < $targetFacultyHours && $attempts < $maxTotalAttempts) {
        $attempts++;
        
        $remainingFacultyHours = $maxFacultyHours - $facultyHours[$facultyId];
        
        if ($remainingFacultyHours <= 0) {
            break;
        }
        
        $validTypes = array_filter($classTypes, function($ct) use ($remainingFacultyHours) {
            return $ct['duration'] <= $remainingFacultyHours;
        });
        
        if (empty($validTypes)) {
            break;
        }
        
        $classTypeKey = array_rand($validTypes);
        $classType = $validTypes[$classTypeKey];
        $duration = $classType['duration'];
        
        // Only use fill sections and ensure they don't exceed max (30 hours)
        $availableSections = array_filter($fillSections, function($s) use ($sectionHours, $duration, $fillSectionMaxHours) {
            return isset($sectionHours[$s]) && $sectionHours[$s] + $duration <= $fillSectionMaxHours;
        });
        
        if (empty($availableSections)) {
            break;
        }
        
        $section = $availableSections[array_rand($availableSections)];
        $year = (int)substr($section, -3, 1);
        
        if (strpos($section, 'IT') === 0) {
            $sectionProgram = 80;
            $courses = $itCourses;
        } elseif (strpos($section, 'CS') === 0) {
            $sectionProgram = 78;
            $courses = $csCourses;
        } else {
            $sectionProgram = 86;
            $courses = $cpeCourses;
        }
        
        $slot = null;
        $selectedDay = null;
        $room = null;
        shuffle($days);
        
        foreach ($days as $day) {
            foreach ($rooms as $tryRoom) {
                $slot = findAvailableSlot($day, $duration, $globalUsedSlots, $dayStart, $dayEnd, $breakTimes, $section, $facultyId, $tryRoom);
                if ($slot !== null) {
                    $selectedDay = $day;
                    $room = $tryRoom;
                    break 2;
                }
            }
        }
        
        if ($slot === null) {
            continue;
        }
        
        $globalUsedSlots[] = [
            'day' => $selectedDay,
            'start' => $slot['start'],
            'end' => $slot['end'],
            'section' => $section,
            'faculty_id' => $facultyId,
            'room' => $room
        ];
        
        $course = $courses[array_rand($courses)];
        $startTime = minutesToTime($slot['start']);
        $endTime = minutesToTime($slot['end']);
        
        $insertedSchedules[] = [
            'user_id' => $facultyId,
            'program' => $sectionProgram,
            'section' => $section,
            'room' => $room,
            'day_of_week' => $selectedDay,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'class_name' => $course . " ({$classType['description']})",
            'year' => $year
        ];
        
        $facultyHours[$facultyId] += $duration;
        $sectionHours[$section] += $duration;
    }
}

// Insert all schedules
$stmt = $pdo->prepare("
    INSERT INTO user_schedules (user_id, program, section, room, day_of_week, start_time, end_time, class_name, year)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

foreach ($insertedSchedules as $sched) {
    $stmt->execute([
        $sched['user_id'],
        $sched['program'],
        $sched['section'],
        $sched['room'],
        $sched['day_of_week'],
        $sched['start_time'],
        $sched['end_time'],
        $sched['class_name'],
        $sched['year']
    ]);
}

echo "<h3>Schedule Generation Complete!</h3>";
echo "<p>Inserted " . count($insertedSchedules) . " schedules</p>";

echo "<h3>Faculty Hours Summary:</h3>";
foreach ($facultyHours as $facultyId => $hours) {
    $prof = array_filter($faculty, function($f) use ($facultyId) { return $f['id'] == $facultyId; });
    $prof = reset($prof);
    $maxHours = $prof['is_parttime'] ? 12 : 40;
    $minHours = $prof['is_parttime'] ? 12 : 30;
    $status = ($hours >= $minHours && $hours <= $maxHours) ? '✓' : '✗';
    $color = ($hours >= $minHours && $hours <= $maxHours) ? 'green' : 'red';
    $type = $prof['is_parttime'] ? '(Part-time)' : '(Full-time)';
    echo "<span style='color:$color;'>$status Faculty {$prof['name']} $type (ID: $facultyId): $hours hours/week (min: $minHours, max: $maxHours)</span><br>";
}

echo "<h3>Primary Section Hours Summary (target: 24-26 hours):</h3>";
foreach ($sections as $section) {
    $hours = $sectionHours[$section] ?? 0;
    $status = ($hours >= 24 && $hours <= 26) ? '✓' : (($hours >= 24 && $hours <= 30) ? '~' : '✗');
    $color = ($hours >= 24 && $hours <= 26) ? 'green' : (($hours >= 24 && $hours <= 30) ? 'orange' : 'red');
    echo "<span style='color:$color;'>$status Section $section: $hours hours/week</span><br>";
}

echo "<h3>Fill Section Hours Summary (target: 27-30 hours):</h3>";
foreach ($fillSections as $section) {
    $hours = $sectionHours[$section] ?? 0;
    $status = ($hours >= 27 && $hours <= 30) ? '✓' : (($hours >= 24 && $hours <= 30) ? '~' : '✗');
    $color = ($hours >= 27 && $hours <= 30) ? 'green' : (($hours >= 24 && $hours <= 30) ? 'orange' : 'red');
    echo "<span style='color:$color;'>$status Section $section: $hours hours/week</span><br>";
}

// Verify no overlapping schedules
echo "<h3>Overlap Verification:</h3>";
$overlapQuery = $pdo->query("
    SELECT a.id as id1, b.id as id2, a.user_id, a.section as section1, b.section as section2, 
           a.room as room1, b.room as room2, a.day_of_week, a.start_time, a.end_time
    FROM user_schedules a
    JOIN user_schedules b ON a.id < b.id 
        AND a.day_of_week = b.day_of_week
        AND a.start_time < b.end_time 
        AND a.end_time > b.start_time
        AND (a.user_id = b.user_id OR a.section = b.section OR a.room = b.room)
");
$overlaps = $overlapQuery->fetchAll(PDO::FETCH_ASSOC);

if (empty($overlaps)) {
    echo "<p style='color:green;'>✓ No overlapping schedules found!</p>";
} else {
    echo "<p style='color:red;'>✗ Found " . count($overlaps) . " overlapping schedules:</p>";
    foreach ($overlaps as $overlap) {
        echo "Schedule {$overlap['id1']} and {$overlap['id2']} overlap on {$overlap['day_of_week']}<br>";
    }
}

echo "<h3>Sample Schedules:</h3>";
$stmt = $pdo->query("
    SELECT us.*, CONCAT(u.first_name, ' ', u.last_name) as faculty_name
    FROM user_schedules us
    JOIN users u ON us.user_id = u.id
    ORDER BY us.section, us.day_of_week, us.start_time
    LIMIT 30
");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Faculty</th><th>Section</th><th>Program</th><th>Day</th><th>Time</th><th>Room</th><th>Course</th></tr>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>{$row['faculty_name']}</td>";
    echo "<td>{$row['section']}</td>";
    echo "<td>{$row['program']}</td>";
    echo "<td>{$row['day_of_week']}</td>";
    echo "<td>{$row['start_time']} - {$row['end_time']}</td>";
    echo "<td>{$row['room']}</td>";
    echo "<td>{$row['class_name']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p><a href='check_sections.php'>Check sections and teams</a></p>";
