# Professor Assignment System - Quick Start Guide

## 🎯 What Was Added

A complete professor assignment system that manages:
- **Section Assignments**: Admin assigns professors to teaching sections
- **Team Assignments**: Admin assigns specific professors to teams for title_defense and above
- **Faculty Workflow**: Faculty can accept or reject team assignments
- **Audit Trail**: Track all assignment changes

---

## 📊 Database Tables Created

### 1. **section_professors**
Links professors to teaching sections. Use for title_proposal defenses.

**Fields**: id, section_id, professor_id, assignment_type, status, assigned_at

### 2. **team_professor_assignments**  
Assignments of professors to specific teams for defenses. Requires faculty acceptance.

**Fields**: id, team_id, professor_id, defense_type, assignment_status, accepted_at

### 3. **professor_assignment_history**
Audit trail of all assignment changes.

**Fields**: id, team_id, professor_id, old_status, new_status, action_by, action_notes

---

## 🚀 How to Use

### For Admins (usertype 0)

1. **Open Admin Panel**:
   ```
   http://localhost/admin/professor_assignments.php
   ```

2. **Assign Professors to Sections**:
   - Select section from dropdown
   - Choose professor
   - Click "Assign"
   - View all section assignments

3. **Assign Professors to Teams**:
   - Select a team (shows current defense type)
   - Select professor
   - Add optional notes
   - System only allows assignment for title_defense and above

### For Faculty (usertype 2)

1. **View Pending Assignments**:
   ```
   http://localhost/profile/my_assignments.php
   ```

2. **Manage Assignments**:
   - **Accept**: Click "Accept" button to confirm assignment
   - **Reject**: Click "Reject" and provide reason (admin will be notified)
   - Accepted assignments move to "My Accepted Assignments" section

3. **View Team Details**:
   - Click "View Team" link in accepted assignments
   - Navigate to decision-support page

---

## 📋 Assignment Rules

| Defense Type | Professor Assignment | Requirement |
|---|---|---|
| title_proposal | Optional | Uses section professors |
| title_defense | Required | Must be assigned before defense |
| final_defense | Required | Must be assigned before defense |
| re-defense | Required | Must be assigned before defense |

---

## 🔌 API Endpoints

All endpoints at: `/api/professor_assignments.php?action=...`

### Admin Endpoints

**Assign to Section**:
```php
POST action=assign_professor_to_section
  section_id: int
  professor_id: int
  assignment_type: 'primary'|'secondary'|'tertiary'
```

**Assign to Team**:
```php
POST action=assign_professor_to_team
  team_id: int
  professor_id: int
  defense_type: 'title_defense'|'final_defense'|'re-defense'
  notes: string (optional)
```

**List Section Professors**:
```php
GET action=list_section_professors&section_id=1
```

### Faculty Endpoints

**List Pending Assignments**:
```php
GET action=list_pending
```

**Accept Assignment**:
```php
POST action=accept_assignment
  assignment_id: int
```

**Reject Assignment**:
```php
POST action=reject_assignment
  assignment_id: int
  reason: string
```

### Public Endpoints

**Get Team Professors**:
```php
GET action=list_team_professors&team_id=5&defense_type=title_defense
```

**Get Assignment Status**:
```php
GET action=get_assignment_status&team_id=5&defense_type=title_defense
```

---

## 🔗 Integration Points

### To show assigned professors on home page:
```php
$stmt = $pdo->prepare("
    SELECT u.first_name, u.last_name, tpa.assignment_status
    FROM team_professor_assignments tpa
    JOIN users u ON tpa.professor_id = u.id
    WHERE tpa.team_id = ? AND tpa.defense_type = ?
");
$stmt->execute([$teamId, $defenseType]);
$professors = $stmt->fetchAll();
```

### To show pending assignments for faculty:
```php
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count FROM team_professor_assignments
    WHERE professor_id = ? AND assignment_status = 'pending'
");
$stmt->execute([$userId]);
$pending = $stmt->fetch()['count'];
```

---

## ✅ Testing Checklist

- [ ] Admin can access professor assignments page
- [ ] Admin can assign professor to section
- [ ] Admin can assign professor to team (title_defense+)
- [ ] Faculty can see pending assignments
- [ ] Faculty can accept assignment
- [ ] Faculty can reject assignment with reason
- [ ] Accepted assignments appear in "My Accepted Assignments"
- [ ] Assignment history tracks all changes
- [ ] API endpoints return correct data
- [ ] Students can see assigned professors

---

## 📁 File Locations

| File | Purpose | Access |
|---|---|---|
| `/admin/professor_assignments.php` | Admin assignment UI | Admin only |
| `/profile/my_assignments.php` | Faculty pending assignments | Faculty only |
| `/api/professor_assignments.php` | Backend API | All (restricted by action) |
| `/api/professor_assignments_migration.sql` | Database migration | SQL file |

---

## 🔐 Security

✅ All endpoints check user authentication
✅ Permission checks on each action (admin/faculty/student)
✅ Prepared statements prevent SQL injection
✅ Faculty can only accept/reject their own assignments
✅ Assignment history logs all changes with actor

---

## 💡 Next Steps

1. **Test the Admin Panel**: Assign a few professors to sections
2. **Test Faculty View**: Accept/reject an assignment
3. **Integrate with Home Page**: Show pending assignments count for faculty
4. **Add Email Notifications**: Notify faculty when assigned (optional)
5. **Create Report**: View all assignments by section/professor

---

## 🆘 Troubleshooting

**Q: Faculty doesn't see assignments**
- Check database: `SELECT * FROM team_professor_assignments WHERE professor_id = X`
- Verify `assignment_status = 'pending'`

**Q: Can't assign professor to team**
- Ensure defense_type is 'title_defense', 'final_defense', or 're-defense'
- Check professor exists and is usertype 2

**Q: Assignment status not updating**
- Verify form data is being sent correctly
- Check browser console for API errors
- Review `/opt/lampp/logs/php_error_log`

**Q: Permission denied**
- Verify logged-in user type (0=admin, 2=faculty)
- Check session is active

---

## 📞 Support

For issues or questions, check:
1. `/PROFESSOR_ASSIGNMENTS_GUIDE.md` - Full technical documentation
2. Database logs: Check query errors
3. PHP logs: `/opt/lampp/logs/php_error_log`
4. Browser console: Check JavaScript errors

---

**System Status**: ✅ Ready to Use

Last Updated: November 24, 2025
