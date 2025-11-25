# Technical Changelog - Program Filter Fix

## File: `/opt/lampp/htdocs/assets/includes/program_filter.php`

### Summary
Fixed 4 critical functions that had incorrect SQL JOINs with non-existent database tables. Updated to use actual schema where faculty programs are stored in `users.program` field.

---

## Change 1: `getVisibleProgramsFilter()` - Faculty Logic

### Location: Lines 50-70 (approximately)

### Before
```php
if ($usertype === 2) {
    // Get faculty's assigned section
    $stmt = $pdo->prepare("
        SELECT DISTINCT p.id, p.name, p.specialization, p.college
        FROM programs p
        JOIN sections s ON s.program_id = p.id              // ❌ TABLE DOESN'T EXIST
        JOIN section_professors sp ON sp.section_id = s.id   // ❌ WRONG RELATIONSHIP
        WHERE sp.professor_id = :user_id
        ORDER BY p.name
    ");
    // ... returns programs array used for placeholder generation
}
```

### After
```php
if ($usertype === 2) {
    // Get faculty's program from users table
    $stmt = $pdo->prepare("
        SELECT DISTINCT program
        FROM users
        WHERE id = :user_id AND usertype = 2
    ");
    $stmt->execute([':user_id' => $userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result || !$result['program']) {
        return ['sql' => '1=0', 'params' => []];
    }
    
    $userProgram = $result['program'];
    return [
        'sql' => "CONCAT(programs.name, CASE WHEN programs.specialization != '' 
                  THEN CONCAT(' - ', programs.specialization) ELSE '' END) = :faculty_program",
        'params' => [':faculty_program' => $userProgram]
    ];
}
```

**Key Changes:**
- ✅ Removed JOIN with non-existent `sections` table
- ✅ Query `users.program` field directly
- ✅ Compare program names using CONCAT (handles specialization)

---

## Change 2: `getVisiblePrograms()` - Faculty Logic

### Location: Lines ~130-145

### Before
```php
if ($usertype === 2) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT p.id, p.name, p.specialization, p.college
        FROM programs p
        JOIN sections s ON s.program_id = p.id              // ❌ DOESN'T EXIST
        JOIN section_professors sp ON sp.section_id = s.id   // ❌ WRONG JOIN
        WHERE sp.professor_id = :user_id
        ORDER BY p.name ASC
    ");
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

### After
```php
if ($usertype === 2) {
    $stmt = $pdo->prepare("
        SELECT p.id, p.name, p.specialization, p.college
        FROM programs p
        JOIN users u ON CONCAT(p.name, CASE WHEN p.specialization != '' 
                              THEN CONCAT(' - ', p.specialization) ELSE '' END) = u.program
        WHERE u.id = :user_id AND u.usertype = 2
        LIMIT 1
    ");
    $stmt->execute([':user_id' => $userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? [$result] : [];
}
```

**Key Changes:**
- ✅ JOIN programs to users via program name matching
- ✅ Return single program (faculty has only one program)
- ✅ Handle specialization in CONCAT

---

## Change 3: `getVisibleUsers()` - Faculty Logic

### Location: Lines ~240-260

### Before
```php
if ($usertype === 2) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT u.* FROM users u
        JOIN sections s ON s.id = u.section_id              // ❌ DOESN'T EXIST
        JOIN section_professors sp ON sp.section_id = s.id   // ❌ WRONG JOIN
        WHERE sp.professor_id = :user_id
        ORDER BY u.first_name, u.last_name
    ");
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

### After
```php
if ($usertype === 2) {
    // Get faculty's own program
    $stmt = $pdo->prepare("
        SELECT program
        FROM users
        WHERE id = :user_id AND usertype = 2
    ");
    $stmt->execute([':user_id' => $userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result || !$result['program']) {
        return [];
    }
    
    // Get all users with same program
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE program = :program
        ORDER BY first_name, last_name
    ");
    $stmt->execute([':program' => $result['program']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

**Key Changes:**
- ✅ Two-step query: get faculty's program, then get users from that program
- ✅ Simple direct comparison (users.program = :program)
- ✅ More efficient than complex JOINs

---

## Change 4: `getVisibleTeams()` - Program Chair & Faculty Logic

### Location: Lines ~295-330

### Before (Program Chair)
```php
if ($userId !== 0 && $usertype === 0) {
    $stmt = $pdo->prepare("
        SELECT t.*, rt.title FROM teams t 
        LEFT JOIN research_titles rt ON t.id = rt.team_id
        JOIN programs p ON t.program = p.id                 // ❌ WRONG: t.program is VARCHAR name, not ID
        WHERE p.college = :college
        ORDER BY t.name
    ");
    $stmt->execute([':college' => $userCollege]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

### After (Program Chair)
```php
if ($userId !== 0 && $usertype === 0) {
    $stmt = $pdo->prepare("
        SELECT t.*, rt.title FROM teams t 
        LEFT JOIN research_titles rt ON t.id = rt.team_id
        JOIN programs p ON CONCAT(p.name, CASE WHEN p.specialization != '' 
                          THEN CONCAT(' - ', p.specialization) ELSE '' END) = t.program
        WHERE p.college = :college
        ORDER BY t.name
    ");
    $stmt->execute([':college' => $userCollege]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

### Before (Faculty)
```php
if ($usertype === 2) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT t.*, rt.title FROM teams t 
        LEFT JOIN research_titles rt ON t.id = rt.team_id
        JOIN programs p ON t.program = p.id                 // ❌ WRONG TYPE
        JOIN sections s ON s.program_id = p.id              // ❌ DOESN'T EXIST
        JOIN section_professors sp ON sp.section_id = s.id   // ❌ WRONG
        WHERE sp.professor_id = :user_id
        ORDER BY t.name
    ");
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

### After (Faculty)
```php
if ($usertype === 2) {
    $stmt = $pdo->prepare("
        SELECT program
        FROM users
        WHERE id = :user_id AND usertype = 2
    ");
    $stmt->execute([':user_id' => $userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result || !$result['program']) {
        return [];
    }
    
    // Get all teams from same program
    $stmt = $pdo->prepare("
        SELECT t.*, rt.title FROM teams t 
        LEFT JOIN research_titles rt ON t.id = rt.team_id
        WHERE t.program = :program
        ORDER BY t.name
    ");
    $stmt->execute([':program' => $result['program']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

**Key Changes:**
- ✅ Program Chair: Use CONCAT for program name matching (handles specialization)
- ✅ Faculty: Simple direct comparison (t.program = :program)
- ✅ Removed all non-existent table JOINs

---

## Summary of Changes

| Change | Function | Type | Impact |
|--------|----------|------|--------|
| 1 | `getVisibleProgramsFilter()` | SQL Fix | Faculty filter now works |
| 2 | `getVisiblePrograms()` | SQL Fix | Faculty program list now works |
| 3 | `getVisibleUsers()` | SQL Fix | Faculty user filtering now works |
| 4 | `getVisibleTeams()` (2 parts) | SQL Fix | Both chair and faculty team filtering now work |

## Validation

✅ All 4 functions now use actual database schema
✅ No references to non-existent tables
✅ Faculty filtering uses `users.program` field
✅ Program Chair filtering uses program name + college matching
✅ PHP syntax validation: PASS

---

**Date:** November 25, 2025
**Status:** ✅ DEPLOYED
**Lines Changed:** ~60 (4 functions, multiple SQL statements)
**Files Modified:** 1 (`program_filter.php`)
**Breaking Changes:** None (fixes existing code that was broken)
