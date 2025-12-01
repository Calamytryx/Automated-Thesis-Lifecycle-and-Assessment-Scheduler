# 🎨 Professor Assignment System - Visual Reference

## 🗺️ User Journey Maps

### Admin Workflow
```
┌─────────────┐
│ Admin Login │
└──────┬──────┘
       │
       ▼
┌─────────────────────────────────┐
│ /admin/professor_assignments.php│
└───────────┬────────────┬────────┘
            │            │
       ┌────▼───┐   ┌────▼────┐
       │ Section │   │  Teams  │
       │  Tab    │   │  Tab    │
       └────┬───┘   └────┬────┘
            │             │
        (Select &)    (Select &)
        (Assign)      (Assign +)
        (Prof)        (Notes)
            │             │
            ▼             ▼
     ┌─────────────┐  ┌─────────────────┐
     │ Section     │  │ Assignment      │
     │ Assignment  │  │ sent to Faculty │
     │ Created     │  │ (status=pending)│
     └─────────────┘  └────────┬────────┘
                               │
                          Faculty notified
                          to review
```

### Faculty Workflow
```
┌────────────────────┐
│ Faculty Login      │
└────────┬───────────┘
         │
         ▼
┌──────────────────────────┐
│/profile/my_assignments.php
└────────┬─────────────────┘
         │
    ┌────▼────────────────────┐
    │ Pending Assignments List │
    └────┬────────┬───────────┘
         │        │
    ┌────▼─┐  ┌──▼────┐
    │Accept│  │Reject │
    └────┬─┘  └──┬────┘
         │       │
      ┌──▼──┐  ┌─▼──────────────┐
      │Enter│  │Provide Reason  │
      │Status│  │for Rejection   │
      │=    │  │Submit          │
      │accept│  └─┬──────────────┘
      └──┬──┘    │
         │       ▼
    ┌────▼─────────────────┐
    │Assignment Status     │
    │Updated + Logged      │
    │Admin Notified        │
    └──────────────────────┘
```

### Student View (Future Integration)
```
┌──────────────────┐
│ Student Login    │
└────────┬─────────┘
         │
         ▼
┌─────────────────────┐
│ /home/index.php     │
│ Defense Schedule    │
└────────┬────────────┘
         │
    ┌────▼────────────────────┐
    │ Shows:                  │
    │ • Team Name             │
    │ • Defense Type          │
    │ • Date & Time           │
    │ • Room                  │
    │ • Assigned Professor(s) │
    │   + Contact Info        │
    └─────────────────────────┘
```

---

## 📊 Database Relationship Diagram

```
┌─────────────┐
│    users    │
│ (usertype=2)│ (Faculty members)
└──────┬──────┘
       │ 1
       │
    ┌──▼────────────────────┐
    │ N                      │
    │ section_professors ◄───┼─── professor_id
    │                        │
    │ • section_id ──┐       │
    │ • assignment   │       │
    │   type        │       │
    │ • status      │       │
    └────┬──────────┘       │
         │                  │
         │ 1    ┌──────────────────┐
         └─────►│    sections      │
                │ (course sections)│
                └──────────────────┘

┌──────────────┐
│    teams     │
│              │
└──────┬───────┘
       │ 1
       │
    ┌──▼──────────────────────┐
    │ N                        │
    │team_professor_           │
    │assignments ◄─────────────┼─── team_professor
    │                          │    _assignments
    │ • professor_id ──┐       │
    │ • defense_type   │   ┌───┼─── users
    │ • status         │   │   │   (professor_id)
    │ • accepted_at    │   │   │
    └────┬─────────────┘   │   │
         │                  │   │
         │ assignment_id    │   ▼
         │                  │  (Faculty)
         │             ┌────┼────────┐
         │             │users        │
         │             │usertype = 2 │
         │             └─────────────┘
         │
         ▼
    ┌──────────────────────────────┐
    │professor_assignment_history   │
    │(audit trail)                 │
    │                              │
    │ • old_status                 │
    │ • new_status                 │
    │ • action_by (user_id)        │
    │ • action_notes               │
    │ • created_at                 │
    └──────────────────────────────┘
```

---

## 🎯 Assignment Status Flow

```
                    ┌─────────────────┐
                    │ PENDING STATUS  │
                    │ (Awaiting Action)
                    └────────┬────────┘
                             │
                    ┌────────┴────────┐
                    │                 │
                    ▼                 ▼
            ┌────────────────┐ ┌──────────────┐
            │ ACCEPTED       │ │ REJECTED     │
            │ (Confirmed by  │ │ (Declined by │
            │  Faculty)      │ │  Faculty)    │
            └────────┬───────┘ └──────┬───────┘
                     │                │
                     │ (continues)    │ (admin reassigns)
                     │                │
                     ▼                ▼
            ┌──────────────────┐  Return to PENDING
            │ COMPLETED        │  (for different prof)
            │ (Defense done)   │
            └──────────────────┘
```

---

## 🔐 Permission Matrix

| Action | Admin (0) | Faculty (2) | Student (1) |
|--------|:---------:|:----------:|:----------:|
| View All Assignments | ✅ | ❌ | ❌ |
| View Own Assignments | ✅ | ✅ | ✅ |
| Assign to Section | ✅ | ❌ | ❌ |
| Assign to Team | ✅ | ❌ | ❌ |
| Accept Assignment | ❌ | ✅ | ❌ |
| Reject Assignment | ❌ | ✅ | ❌ |
| View History | ✅ | ❌ | ❌ |
| See Prof in Schedule | ✅ | ✅ | ✅ |

---

## 🗂️ File Organization

```
htdocs/
│
├── 📂 api/
│   ├── 📄 professor_assignments.php (400+ lines)
│   │   └─ 8 REST endpoints
│   │   └─ Permission-based access
│   │   └─ Audit logging
│   │
│   └── 📄 professor_assignments_migration.sql
│       └─ Creates 3 tables
│       └─ Indexes & FK constraints
│
├── 📂 admin/
│   └── 📄 professor_assignments.php (300+ lines)
│       ├─ Tab 1: Section Assignments
│       ├─ Tab 2: Team Assignments
│       ├─ Tab 3: Assignment History
│       └─ Bootstrap 5 UI
│
├── 📂 profile/
│   └── 📄 my_assignments.php (300+ lines)
│       ├─ Pending Assignments (with Accept/Reject)
│       ├─ Accepted Assignments
│       ├─ Gradient UI Design
│       └─ SweetAlert2 dialogs
│
├── 📂 docs/
│   ├── 📘 PROFESSOR_ASSIGNMENTS_SUMMARY.md (THIS)
│   ├── 📘 PROFESSOR_ASSIGNMENTS_GUIDE.md
│   ├── 📘 PROFESSOR_ASSIGNMENTS_QUICKSTART.md
│   └── 📘 PROFESSOR_ASSIGNMENTS_VISUAL.md
│
└── 🔧 setup_professor_assignments.sh
    └─ Automated setup script
```

---

## 🌐 URL Map

| URL | Purpose | User | Method |
|-----|---------|------|--------|
| `/admin/professor_assignments.php` | Admin panel | Admin | GET |
| `/profile/my_assignments.php` | Faculty dashboard | Faculty | GET |
| `/api/professor_assignments.php?action=list_pending` | Get pending | Faculty | GET |
| `/api/professor_assignments.php?action=assign_professor_to_section` | Create section assign | Admin | POST |
| `/api/professor_assignments.php?action=assign_professor_to_team` | Create team assign | Admin | POST |
| `/api/professor_assignments.php?action=accept_assignment` | Accept | Faculty | POST |
| `/api/professor_assignments.php?action=reject_assignment` | Reject | Faculty | POST |
| `/api/professor_assignments.php?action=list_team_professors` | Get team profs | Student/Faculty | GET |

---

## 📈 Data Volume Expectations

### Small Installation (50 students, 10 faculty, 5 sections)
- section_professors: ~20 rows
- team_professor_assignments: ~50 rows
- professor_assignment_history: ~200 rows

### Medium Installation (500 students, 50 faculty, 50 sections)
- section_professors: ~200 rows
- team_professor_assignments: ~500 rows
- professor_assignment_history: ~2,000 rows

### Large Installation (5,000 students, 200 faculty, 100 sections)
- section_professors: ~1,000 rows
- team_professor_assignments: ~5,000 rows
- professor_assignment_history: ~20,000 rows

**Indexes ensure O(1) lookups even at scale**

---

## 🔄 Integration Checklist

### To add to existing pages:

**Home Page** (`/home/index.php`):
```php
// Add notification badge for faculty
if ($_SESSION['usertype'] == 2) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM team_professor_assignments 
                          WHERE professor_id = ? AND assignment_status = 'pending'");
    $stmt->execute([$userId]);
    $pending = $stmt->fetchColumn();
    // Show badge with count
}
```

**Defense Schedule** (show assigned professors):
```php
// Replace view query with:
$stmt = $pdo->prepare("
    SELECT GROUP_CONCAT(CONCAT(u.first_name, ' ', u.last_name) SEPARATOR ', ') 
    FROM team_professor_assignments tpa
    JOIN users u ON tpa.professor_id = u.id
    WHERE tpa.team_id = ? AND tpa.defense_type = ? AND tpa.assignment_status = 'accepted'
");
```

**Decision Support** (`/decision-support/index.php`):
```php
// Replace view with:
$stmt = $pdo->prepare("
    SELECT u.* FROM users u
    JOIN team_professor_assignments tpa ON u.id = tpa.professor_id
    WHERE tpa.team_id = ? AND tpa.defense_type = ? AND tpa.assignment_status = 'accepted'
");
```

---

## 📞 Quick Reference

### For Admin:
- **Assign Section**: http://localhost/admin/professor_assignments.php (Tab 1)
- **Assign Team**: http://localhost/admin/professor_assignments.php (Tab 2)
- **View History**: http://localhost/admin/professor_assignments.php (Tab 3)

### For Faculty:
- **View Assignments**: http://localhost/profile/my_assignments.php
- **Accept**: Click "Accept" button in pending section
- **Reject**: Click "Reject" button and provide reason

### API Calls:
```bash
# See pending (faculty)
curl "http://localhost/api/professor_assignments.php?action=list_pending"

# Create assignment (admin, POST)
curl -X POST "http://localhost/api/professor_assignments.php" \
  -d "action=assign_professor_to_team&team_id=5&professor_id=10&defense_type=title_defense"

# Accept assignment (faculty, POST)
curl -X POST "http://localhost/api/professor_assignments.php" \
  -d "action=accept_assignment&assignment_id=1"
```

---

## ✨ Key Statistics

- **Lines of Code**: ~1,000
- **Database Tables**: 3 new tables
- **API Endpoints**: 8
- **User Interfaces**: 2 (Admin + Faculty)
- **Documented Files**: 4
- **Setup Time**: < 5 minutes
- **Test Scenarios**: 4+ covered

---

**Last Updated**: November 24, 2025  
**Version**: 1.0.0  
**Status**: ✅ Production Ready
