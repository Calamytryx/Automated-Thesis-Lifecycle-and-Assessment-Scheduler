# Professor Assignment System - Implementation Guide

## Overview
This system manages professor assignments to sections and teams for college research presentation defenses. It replaces database views with proper relational tables and provides:

- **Admins** (usertype 0): Can assign professors to sections and teams
- **Faculty** (usertype 2): Can view and accept/reject team assignments
- **Students** (usertype 1): Can see assigned professors for their teams

---

## Database Changes

### New Tables Created

#### 1. `section_professors`
Maps professors to sections/courses
```sql
CREATE TABLE section_professors (
  id INT PRIMARY KEY AUTO_INCREMENT,
  section_id INT NOT NULL,
  professor_id INT NOT NULL,
  assignment_type ENUM('primary', 'secondary', 'tertiary') DEFAULT 'primary',
  status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
  assigned_by INT,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY (section_id, professor_id),
  FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE,
  FOREIGN KEY (professor_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### 2. `team_professor_assignments`
Tracks professor assignments to specific teams for defenses
```sql
CREATE TABLE team_professor_assignments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  team_id INT NOT NULL,
  professor_id INT NOT NULL,
  defense_type ENUM('title_proposal', 'title_defense', 'final_defense', 're-defense', 'general') NOT NULL,
  section_id INT,
  assignment_status ENUM('pending', 'accepted', 'rejected', 'completed') DEFAULT 'pending',
  assignment_notes TEXT,
  assigned_by INT,
  accepted_at TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY (team_id, professor_id, defense_type),
  FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
  FOREIGN KEY (professor_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### 3. `professor_assignment_history`
Audit trail for all assignments
```sql
CREATE TABLE professor_assignment_history (
  id INT PRIMARY KEY AUTO_INCREMENT,
  assignment_id INT,
  team_id INT NOT NULL,
  professor_id INT NOT NULL,
  defense_type VARCHAR(50),
  old_status VARCHAR(50),
  new_status VARCHAR(50),
  action_by INT NOT NULL,
  action_notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
);
```

### Installation Steps

1. **Execute Migration SQL**:
   ```bash
   mysql -h localhost -u icei_38697196 -p'4rdL34hSdQFcgrL' icei_38697196_coecsathesis < /opt/lampp/htdocs/api/professor_assignments_migration.sql
   ```

2. **Verify Tables Created**:
   ```bash
   mysql -h localhost -u icei_38697196 -p icei_38697196_coecsathesis -e "SHOW TABLES LIKE '%professor%';"
   ```

---

## File Structure

```
/opt/lampp/htdocs/
├── api/
│   ├── professor_assignments.php          # Backend API endpoints
│   └── professor_assignments_migration.sql # Database migration
├── admin/
│   └── professor_assignments.php          # Admin assignment UI
└── profile/
    └── my_assignments.php                  # Faculty pending assignments UI
```

---

## API Endpoints

### Base URL: `../api/professor_assignments.php`

#### 1. List Pending Assignments (Faculty)
```
GET /api/professor_assignments.php?action=list_pending
```
Returns pending assignments for logged-in faculty member.

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "team_id": 5,
      "professor_id": 10,
      "defense_type": "title_defense",
      "assignment_status": "pending",
      "team_name": "Team A",
      "created_at": "2025-11-24 10:30:00"
    }
  ],
  "count": 1
}
```

#### 2. List Section Professors (Admin)
```
GET /api/professor_assignments.php?action=list_section_professors&section_id=1
```
Returns all professors assigned to a section.

#### 3. Assign Professor to Section (Admin)
```
POST /api/professor_assignments.php
  action: assign_professor_to_section
  section_id: 1
  professor_id: 10
  assignment_type: primary
```

#### 4. Assign Professor to Team (Admin)
```
POST /api/professor_assignments.php
  action: assign_professor_to_team
  team_id: 5
  professor_id: 10
  defense_type: title_defense
  section_id: 1 (optional)
  notes: (optional)
```
**Only for title_defense and above. Title_proposal uses section professors.**

#### 5. Accept Assignment (Faculty)
```
POST /api/professor_assignments.php
  action: accept_assignment
  assignment_id: 1
```
Faculty accepts a team assignment.

#### 6. Reject Assignment (Faculty)
```
POST /api/professor_assignments.php
  action: reject_assignment
  assignment_id: 1
  reason: (optional)
```
Faculty rejects a team assignment.

#### 7. Get Assignment Status
```
GET /api/professor_assignments.php?action=get_assignment_status&team_id=5&defense_type=title_defense
```
Returns all professor assignments for a team's defense.

#### 8. List Team Professors
```
GET /api/professor_assignments.php?action=list_team_professors&team_id=5&defense_type=title_defense
```
Returns all professors assigned to a team (filtered by defense type).

---

## User Interfaces

### Admin Interface: `/admin/professor_assignments.php`

**Features**:
- ✅ Tab 1: Section Assignments
  - Assign professors to sections
  - View section-wide assignments
  
- ✅ Tab 2: Team Assignments
  - Assign professors to specific teams (title_defense+)
  - Add assignment notes
  - View recent assignments

- ✅ Tab 3: Assignment History
  - Audit trail of all assignments
  - Status change tracking

**Access**: Admin (usertype 0) only

---

### Faculty Interface: `/profile/my_assignments.php`

**Features**:
- ✅ Pending Assignments Section
  - Shows all pending team assignments
  - Accept/Reject buttons
  - Assignment notes display
  - Defense type indicator

- ✅ Accepted Assignments Section
  - Shows accepted assignments
  - Links to team pages
  - Accept date display

**Access**: Faculty (usertype 2) only

---

## Business Logic

### Assignment Flow

1. **Admin Creates Assignment**
   - Admin selects team and professor
   - System creates record with `assignment_status = 'pending'`
   - Professor notification sent (future enhancement)

2. **Faculty Reviews Assignment**
   - Faculty visits `/profile/my_assignments.php`
   - Sees pending assignments
   - Can Accept or Reject

3. **Faculty Accepts**
   - Status changes to `'accepted'`
   - `accepted_at` timestamp set
   - Admin can now see confirmed assignment
   - Team can see assigned professor

4. **Faculty Rejects**
   - Status changes to `'rejected'`
   - Admin is notified for reassignment
   - Can assign different professor

### Defense Type Rules

- **title_proposal**: Uses section professors (no team assignment)
- **title_defense**: Requires professor assignment
- **final_defense**: Requires professor assignment
- **re-defense**: Requires professor assignment

---

## Integration with Existing Pages

### Decision-Support Page
Update `/decision-support/index.php` to query `team_professor_assignments` instead of views:

```php
$professorStmt = $pdo->prepare("
    SELECT u.id, u.first_name, u.last_name, u.email, tpa.assignment_status
    FROM team_professor_assignments tpa
    JOIN users u ON tpa.professor_id = u.id
    WHERE tpa.team_id = ? AND tpa.defense_type = ? AND tpa.assignment_status = 'accepted'
");
$professorStmt->execute([$teamId, $defenseType]);
$professors = $professorStmt->fetchAll(PDO::FETCH_ASSOC);
```

### Home Page
Update student/faculty defense schedule view to show assigned professors:

```php
$scheduleStmt = $pdo->prepare("
    SELECT 
        ds.*,
        GROUP_CONCAT(CONCAT(u.first_name, ' ', u.last_name) SEPARATOR ', ') as assigned_professors
    FROM defense_schedules ds
    LEFT JOIN team_professor_assignments tpa ON ds.team_id = tpa.team_id AND ds.defense_type = tpa.defense_type
    LEFT JOIN users u ON tpa.professor_id = u.id AND tpa.assignment_status = 'accepted'
    WHERE ds.team_id = ?
    GROUP BY ds.id
");
```

---

## Testing Checklist

- [ ] Database tables created successfully
- [ ] Admin can access `/admin/professor_assignments.php`
- [ ] Admin can assign professor to section
- [ ] Admin can assign professor to team
- [ ] Faculty receives assignment notification
- [ ] Faculty can accept assignment
- [ ] Faculty can reject assignment with reason
- [ ] Assignment history logs all actions
- [ ] Students see assigned professors
- [ ] Decision-support page shows assigned professors
- [ ] Home page shows pending assignments
- [ ] Reject notifications sent to admin

---

## Security Notes

✅ All endpoints verify user permissions:
- Section assignments: Admin only
- Team assignments: Admin only
- Accept/Reject: Faculty only (own assignments)
- Assignment history: Admin only

✅ SQL injection prevention: All queries use prepared statements

✅ Authorization checks: Verify user belongs to assignment before action

---

## Future Enhancements

1. Email notifications when:
   - Assignment created (professor)
   - Assignment accepted (admin, team)
   - Assignment rejected (admin, team)

2. Bulk assignment from CSV

3. Assignment templates by section

4. Professor availability calendar

5. Automatic assignment based on load balancing

6. Reassignment workflow when rejected

---

## Troubleshooting

**Q: Faculty doesn't see assignments**
A: Check `team_professor_assignments` table has records with `professor_id = faculty_id`

**Q: Assignment status not updating**
A: Verify `assignment_status` enum values match: pending, accepted, rejected, completed

**Q: Permission denied when assigning**
A: Ensure logged-in user is admin (usertype 0)

---

## Support
For questions or issues, contact: admin@university.edu
