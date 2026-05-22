<?php

function teamCodeNormalizeText(?string $value): string {
    $value = strtolower(trim((string)$value));
    $value = preg_replace('/\s+/', ' ', $value);
    $value = preg_replace('/[^a-z0-9\s.-]/', '', $value);
    return trim($value);
}

function teamCodeProgramDisplay(array $programRow): string {
    $name = trim((string)($programRow['name'] ?? ''));
    $specialization = trim((string)($programRow['specialization'] ?? ''));

    if ($name === '') {
        return '';
    }

    return $specialization !== '' ? $name . ' - ' . $specialization : $name;
}

function teamCodeStripDegreePrefix(string $programDisplay): string {
    $programDisplay = trim($programDisplay);
    $patterns = [
        '/^bachelor of science in\s+/i',
        '/^bachelor of arts in\s+/i',
        '/^bachelor of\s+/i',
        '/^bachelor in\s+/i',
        '/^master of arts in\s+/i',
        '/^master of science in\s+/i',
        '/^master in\s+/i',
        '/^ph\.d\. in\s+/i',
        '/^doctor of philosophy in\s+/i',
        '/^doctor of\s+/i',
        '/^juris doctor$/i',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $programDisplay)) {
            return trim(preg_replace($pattern, '', $programDisplay));
        }
    }

    return $programDisplay;
}

function teamCodeWordAcronym(string $text, int $maxLength = 5): string {
    $text = teamCodeNormalizeText($text);
    $text = str_replace(['-', '.'], ' ', $text);

    $stopWords = ['of', 'and', 'in', 'the', 'for', 'to', 'with'];
    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    $letters = '';

    foreach ($words as $word) {
        if (in_array($word, $stopWords, true)) {
            continue;
        }

        $letters .= strtoupper(substr($word, 0, 1));
        if (strlen($letters) >= $maxLength) {
            break;
        }
    }

    return $letters;
}

function teamCodeExplicitMap(): array {
    return [
        'bachelor of science in computer science' => 'CS',
        'bachelor of science in computer science - data science' => 'CS',
        'bachelor of science in computer science - software engineering' => 'CS',
        'bachelor of science in information technology' => 'IT',
        'bachelor of science in information technology - network and information security' => 'IT',
        'bachelor of science in information technology - web and mobile technology' => 'IT',
        'bachelor of science in computer engineering' => 'CpE',
        'bachelor of science in civil engineering' => 'CE',
        'bachelor of science in electrical engineering' => 'EEE',
        'bachelor of science in electronics engineering' => 'ECE',
        'bachelor of science in industrial engineering' => 'IE',
        'bachelor of science in mechanical engineering' => 'ME',
        'bachelor of science in aeronautical engineering' => 'AE',
        'bachelor of science in architecture (arch)' => 'ARCH',
        'juris doctor' => 'JD',
        'master in business administration' => 'MBA',
        'master in public administration' => 'MPA',
        'master of arts in education' => 'MAEd',
        'master in international hospitality management' => 'MIHM',
        'master in international travel and tourism management' => 'MITTM',
        'ph.d. in business management' => 'PhDBM',
        'ph.d. in public policy and management' => 'PhDPPM',
        'ph.d. in international hospitality management' => 'PhDIHM',
        'ph.d. in international tourism management' => 'PhDITM',
        'ph.d. in english language' => 'PhDEL',
    ];
}

function teamCodeDisciplinePrefix(string $programDisplay): string {
    $programDisplay = teamCodeNormalizeText($programDisplay);
    $programDisplay = str_replace([' - ', '(', ')'], ' ', $programDisplay);
    $discipline = teamCodeStripDegreePrefix($programDisplay);

    $explicitDisciplineMap = [
        'computer science' => 'CS',
        'information technology' => 'IT',
        'computer engineering' => 'CpE',
        'civil engineering' => 'CE',
        'electrical engineering' => 'EEE',
        'electronics engineering' => 'ECE',
        'industrial engineering' => 'IE',
        'mechanical engineering' => 'ME',
        'aeronautical engineering' => 'AE',
        'architecture' => 'ARCH',
        'medical technology' => 'MT',
        'pharmacy' => 'PHARM',
        'radiologic technology' => 'RT',
        'biology' => 'BIO',
        'communication' => 'COMM',
        'foreign service' => 'FS',
        'legal studies' => 'LS',
        'early childhood education' => 'ECEd',
        'secondary education' => 'BSEd',
        'psychology' => 'PSYCH',
        'accountancy' => 'BSA',
        'business administration' => 'BBA',
        'customs administration' => 'CA',
        'entrepreneurship' => 'ENTREP',
        'real estate management' => 'REM',
        'business management' => 'BM',
        'public policy and management' => 'PPM',
        'public administration' => 'PA',
        'international hospitality management' => 'IHM',
        'international travel and tourism management' => 'ITTM',
        'english language' => 'EL',
        'educational management' => 'EM',
        'health and wellness' => 'HW',
        'cruise line operations in culinary arts' => 'CLOCA',
        'cruise line operations in hotel services' => 'CLOHS',
        'culinary arts and kitchen operations' => 'CAKO',
        'hotel and restaurant administration' => 'HRA',
        'nutrition and dietetics' => 'ND',
    ];

    if (isset($explicitDisciplineMap[$discipline])) {
        return $explicitDisciplineMap[$discipline];
    }

    $discipline = str_replace(['/', '&'], ' ', $discipline);
    $discipline = preg_replace('/\s+/', ' ', $discipline);

    $acronym = teamCodeWordAcronym($discipline, 5);
    return $acronym !== '' ? $acronym : 'TM';
}

function teamCodeBasePrefixFromProgram(array $programRow): string {
    $programDisplay = teamCodeProgramDisplay($programRow);
    $normalizedDisplay = teamCodeNormalizeText($programDisplay);

    $explicitMap = teamCodeExplicitMap();
    if (isset($explicitMap[$normalizedDisplay])) {
        return $explicitMap[$normalizedDisplay];
    }

    if (str_starts_with($normalizedDisplay, 'juris doctor')) {
        return 'JD';
    }

    if (str_contains($normalizedDisplay, 'ph.d.') || str_contains($normalizedDisplay, 'doctor of philosophy')) {
        $discipline = teamCodeStripDegreePrefix($programDisplay);
        return 'PhD' . teamCodeWordAcronym($discipline, 4);
    }

    if (str_starts_with($normalizedDisplay, 'master ')) {
        $discipline = teamCodeStripDegreePrefix($programDisplay);
        if ($discipline !== '') {
            $acronym = teamCodeWordAcronym($discipline, 5);
            return in_array($acronym, ['MBA', 'MPA', 'MAED', 'MIHM', 'MITTM'], true)
                ? $acronym
                : 'M' . $acronym;
        }
    }

    if (str_starts_with($normalizedDisplay, 'bachelor ')) {
        $discipline = teamCodeStripDegreePrefix($programDisplay);
        return teamCodeDisciplinePrefix($discipline);
    }

    return teamCodeDisciplinePrefix($programDisplay);
}

function teamCodeParseAcademicYear(string $academicYear): ?array {
    $academicYear = trim($academicYear);
    if ($academicYear === '') {
        return null;
    }

    if (!preg_match('/^(\d{4})-(\d{4}),\s*(1st Semester|2nd Semester|Summer)$/i', $academicYear, $matches)) {
        return null;
    }

    $startYear = (int)$matches[1];
    $endYear = (int)$matches[2];
    $semester = trim($matches[3]);

    if ($endYear !== $startYear + 1) {
        return null;
    }

    $academicYearCode = substr((string)$startYear, 2, 2) . substr((string)$endYear, 2, 2);
    $semesterCode = '1';

    if (stripos($semester, '2nd Semester') === 0) {
        $semesterCode = '2';
    } elseif (stripos($semester, 'Summer') === 0) {
        $semesterCode = 'S';
    }

    return [
        'label' => $academicYear,
        'code' => $academicYearCode,
        'semester' => $semester,
        'semester_code' => $semesterCode,
    ];
}

function resolveTeamAcademicYear(PDO $pdo, array $memberIds, int $userId, int $usertype, ?string $explicitAcademicYear = null): array {
    $explicitAcademicYear = trim((string)$explicitAcademicYear);

    if ($explicitAcademicYear !== '') {
        $parsed = teamCodeParseAcademicYear($explicitAcademicYear);
        if (!$parsed) {
            return [
                'success' => false,
                'message' => 'Academic year must use the format 2025-2026, 1st Semester.'
            ];
        }

        return [
            'success' => true,
            'academic_year' => $parsed['label'],
            'academic_year_code' => $parsed['code'],
            'semester_code' => $parsed['semester_code'],
        ];
    }

    $academicYears = [];

    if (!empty($memberIds)) {
        $memberIds = array_values(array_unique(array_map('intval', $memberIds)));
        $placeholders = implode(',', array_fill(0, count($memberIds), '?'));

        $stmt = $pdo->prepare("\n            SELECT DISTINCT sp.academic_year\n            FROM users u\n            JOIN section_professors sp ON sp.section = u.section\n            WHERE u.id IN ($placeholders)\n              AND sp.status = 'active'\n              AND sp.academic_year IS NOT NULL\n              AND sp.academic_year != ''\n        ");
        $stmt->execute($memberIds);
        $academicYears = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    if (empty($academicYears) && $usertype === 2 && $userId > 0) {
        $stmt = $pdo->prepare("\n            SELECT DISTINCT academic_year\n            FROM section_professors\n            WHERE professor_id = ?\n              AND status = 'active'\n              AND academic_year IS NOT NULL\n              AND academic_year != ''\n        ");
        $stmt->execute([$userId]);
        $academicYears = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    $academicYears = array_values(array_unique(array_filter(array_map('trim', $academicYears))));

    if (count($academicYears) === 1) {
        $parsed = teamCodeParseAcademicYear($academicYears[0]);
        if (!$parsed) {
            return [
                'success' => false,
                'message' => 'The selected academic year is not in the expected format.'
            ];
        }

        return [
            'success' => true,
            'academic_year' => $parsed['label'],
            'academic_year_code' => $parsed['code'],
            'semester_code' => $parsed['semester_code'],
        ];
    }

    if (count($academicYears) > 1) {
        return [
            'success' => false,
            'message' => 'Multiple active academic years were found for the selected team. Set one academic year before creating the team.'
        ];
    }

    return [
        'success' => false,
        'message' => 'Academic year must be set before creating teams.'
    ];
}

function generateTeamCode(PDO $pdo, string $programDisplay, string $academicYearLabel, ?int $excludeTeamId = null): array {
    $programDisplay = trim($programDisplay);
    $parsedAcademicYear = teamCodeParseAcademicYear($academicYearLabel);

    if ($programDisplay === '') {
        return [
            'success' => false,
            'message' => 'Program is required for team code generation.'
        ];
    }

    if (!$parsedAcademicYear) {
        return [
            'success' => false,
            'message' => 'Academic year must use the format 2025-2026, 1st Semester.'
        ];
    }

    $prefix = teamCodeBasePrefixFromProgram(['name' => $programDisplay]);
    if ($prefix === '') {
        return [
            'success' => false,
            'message' => 'Unable to generate a team code for the selected program.'
        ];
    }

    $likePrefix = $prefix . $parsedAcademicYear['code'] . '-' . $parsedAcademicYear['semester_code'] . '-%';
    $sql = "SELECT team_code FROM teams WHERE team_code LIKE ?";
    $params = [$likePrefix];

    if ($excludeTeamId !== null) {
        $sql .= " AND id <> ?";
        $params[] = $excludeTeamId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $existingCodes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $nextSequence = 1;
    foreach ($existingCodes as $existingCode) {
        if (preg_match('/-(\d{3})$/', (string)$existingCode, $matches)) {
            $sequence = (int)$matches[1];
            if ($sequence >= $nextSequence) {
                $nextSequence = $sequence + 1;
            }
        }
    }

    $teamCode = sprintf('%s%s-%s-%03d', $prefix, $parsedAcademicYear['code'], $parsedAcademicYear['semester_code'], $nextSequence);

    return [
        'success' => true,
        'team_code' => $teamCode,
        'prefix' => $prefix,
        'academic_year_code' => $parsedAcademicYear['code'],
        'semester_code' => $parsedAcademicYear['semester_code'],
        'sequence' => $nextSequence,
    ];
}
