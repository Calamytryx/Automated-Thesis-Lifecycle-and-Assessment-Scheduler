# Specialization Management System - Implementation Guide

## 📋 Overview

This system allows:
- **Admins**: Create and manage a pool of specializations
- **Research Professors** (teachers with team assignments, NOT advisers): Assign specializations from the pool to teams and users
- **Non-student users**: Receive specialization assignments based on admin/professor decisions

## 🗄️ Database Structure

### New Tables Created

#### 1. `specialization_pool`
Stores the master list of available specializations.

```sql
CREATE TABLE specialization_pool (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    department VARCHAR(255) NULL,
    college VARCHAR(255) NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_specialization (name, department, college)
);
```

#### 2. `user_specializations`
Tracks which specializations are assigned to which users.

```sql
CREATE TABLE user_specializations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    specialization_id INT NOT NULL,
    assigned_by INT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT NULL,
    UNIQUE KEY unique_user_specialization (user_id, specialization_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (specialization_id) REFERENCES specialization_pool(id) ON DELETE CASCADE
);
```

#### 3. `team_specializations`
Tracks which specializations are assigned to which teams.

```sql
CREATE TABLE team_specializations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    specialization_id INT NOT NULL,
    assigned_by INT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT NULL,
    UNIQUE KEY unique_team_specialization (team_id, specialization_id),
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (specialization_id) REFERENCES specialization_pool(id) ON DELETE CASCADE
);
```

## 📂 Files Created

### 1. Database Migration
- **File**: `/opt/lampp/htdocs/create_specialization_pool.sql`
- **Purpose**: Creates all necessary tables with sample data
- **Usage**: Run this SQL file to set up the database

### 2. Backend APIs

#### a) Specialization Pool Management API
- **File**: `/opt/lampp/htdocs/dashboard/includes/specialization_pool_api.php`
- **Purpose**: CRUD operations for specialization pool (Admin only)
- **Actions**:
  - `get_all` - Get all specializations
  - `get_by_id` - Get single specialization
  - `add` - Add new specialization (Admin only)
  - `update` - Update specialization (Admin only)
  - `delete` - Delete specialization (Admin only)
  - `toggle_status` - Activate/deactivate specialization (Admin only)
  - `get_active` - Get only active specializations (All users)
  - `get_by_college` - Get specializations by college

#### b) Specialization Assignment API
- **File**: `/opt/lampp/htdocs/dashboard/includes/specialization_assignment_api.php`
- **Purpose**: Assign specializations to users/teams
- **Access**: Admins and Research Professors with team assignments
- **Actions**:
  - `assign_to_user` - Assign specialization to a user
  - `assign_to_team` - Assign specialization to a team
  - `remove_from_user` - Remove specialization from user
  - `remove_from_team` - Remove specialization from team
  - `get_user_specializations` - Get user's specializations
  - `get_team_specializations` - Get team's specializations
  - `get_my_teams` - Get teams assigned to current professor
  - `get_assignable_users` - Get users that can be assigned specializations

### 3. Frontend Tabs

#### a) Specialization Pool Tab (Admin Only)
- **File**: `/opt/lampp/htdocs/dashboard/includes/tabs/specialization_pool_tab.php`
- **Access**: Admins only
- **Features**:
  - View all specializations
  - Filter by college, department, and status
  - Add new specializations
  - Edit existing specializations
  - Activate/deactivate specializations
  - Delete unused specializations

#### b) Specialization Assignment Tab
- **File**: `/opt/lampp/htdocs/dashboard/includes/tabs/specialization_assignment_tab.php`
- **Access**: Admins and Research Professors
- **Features**:
  - View assigned teams (for research professors)
  - View assignable users (non-students in their teams)
  - Assign specializations to teams
  - Assign specializations to users
  - Remove specializations
  - Add notes to assignments

## 🚀 Installation Steps

### Step 1: Run Database Migration
```bash
# Navigate to your MySQL command line or phpMyAdmin
mysql -u your_username -p your_database < /opt/lampp/htdocs/create_specialization_pool.sql
```

Or in phpMyAdmin:
1. Select your database
2. Go to "Import" tab
3. Choose file: `create_specialization_pool.sql`
4. Click "Go"

### Step 2: Verify File Structure
Ensure all files are in place:
```
/opt/lampp/htdocs/
├── create_specialization_pool.sql
└── dashboard/
    ├── index.php (already modified)
    └── includes/
        ├── specialization_pool_api.php
        ├── specialization_assignment_api.php
        └── tabs/
            ├── specialization_pool_tab.php
            └── specialization_assignment_tab.php
```

### Step 3: Clear Browser Cache
```bash
# Clear cache and reload the dashboard
# Or use Ctrl+Shift+R (Hard reload)
```

## 👥 User Access Levels

### Admin (usertype=0)
✅ Can manage specialization pool (add, edit, delete, activate/deactivate)
✅ Can assign specializations to ANY team or user
✅ Can view all teams and users

**Dashboard Access**:
- "Specialization Pool" tab (Thesis Management section)
- "Assign Specializations" tab (Thesis Management section)

### Research Professor (usertype=2 with team assignments, NOT advisers)
❌ Cannot manage specialization pool
✅ Can assign specializations to their assigned teams
✅ Can assign specializations to non-student members of their assigned teams
✅ Can only view teams assigned to them via `team_professor_assignments`

**Dashboard Access**:
- "Assign Specializations" tab (Defense Management section)

**Important**: Research professors MUST have:
- `usertype = 2` (Faculty)
- At least one accepted assignment in `team_professor_assignments` table
- NOT be an adviser (advisers are in `team_members` with role='adviser')

### Program Chair (usertype=0, program_chair=1)
✅ Same as Admin (full access)

**Dashboard Access**:
- "Specialization Pool" tab (Thesis Management section)
- "Assign Specializations" tab (Thesis Management section)

## 📖 Usage Guide

### For Admins: Managing Specialization Pool

1. **Access the Pool**
   - Navigate to Dashboard
   - Click "Specialization Pool" in Thesis Management section

2. **Add New Specialization**
   - Click "+ Add Specialization" button
   - Fill in:
     - Name (required)
     - Description (optional)
     - College (optional, select from dropdown)
     - Department (optional)
     - Status (Active/Inactive toggle)
   - Click "Save"

3. **Edit Specialization**
   - Click the pencil icon next to any specialization
   - Modify fields
   - Click "Save"

4. **Toggle Status**
   - Click the toggle icon to activate/deactivate
   - Inactive specializations won't appear in assignment dropdown

5. **Delete Specialization**
   - Click the trash icon
   - Confirm deletion
   - Note: Cannot delete if assigned to users/teams

6. **Filter Specializations**
   - Use dropdowns to filter by:
     - College
     - Department
     - Status (Active/Inactive/All)

### For Research Professors: Assigning Specializations

1. **Access Assignment Interface**
   - Navigate to Dashboard
   - Click "Assign Specializations" in Defense Management section

2. **Assign to Team**
   - Click "Assign to Teams" tab
   - Click on a team from "My Teams" list
   - Team's current specializations appear on the right
   - Select a specialization from dropdown
   - (Optional) Add notes
   - Click "Assign Specialization"

3. **Assign to User**
   - Click "Assign to Users" tab
   - Search for a user (if needed)
   - Click on a user from the list
   - User's current specializations appear on the right
   - Select a specialization from dropdown
   - (Optional) Add notes
   - Click "Assign Specialization"

4. **Remove Assignment**
   - Click on assigned team/user
   - Click the X button next to any specialization
   - Confirm removal

## 🔧 Technical Details

### Permission Check Logic

```php
function canAssignSpecializations($pdo, $userId, $usertype) {
    // Admin always has permission
    if ($usertype === 0) {
        return true;
    }
    
    // Faculty with accepted team assignments (not as adviser)
    if ($usertype === 2) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM team_professor_assignments tpa
            WHERE tpa.professor_id = ? 
            AND tpa.assignment_status = 'accepted'
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }
    
    return false;
}
```

### Key Differences: Adviser vs Research Professor

**Adviser**:
- Role stored in `team_members` table with `role = 'adviser'`
- Assigned when team is created
- Cannot assign specializations (unless they also have research professor assignments)

**Research Professor**:
- Assigned via `team_professor_assignments` table
- Assignment must be accepted (`assignment_status = 'accepted'`)
- Can assign specializations to their assigned teams
- Can be assigned to multiple teams

## 🎨 UI Features

### Specialization Pool Tab
- Modern card-based layout
- Real-time filtering
- Modal dialogs for add/edit
- Inline status indicators (Active/Inactive badges)
- Responsive table with action buttons

### Specialization Assignment Tab
- Dual-panel interface (Teams/Users on left, Specializations on right)
- Color-coded selection highlighting
- Real-time search for users
- Informative alerts and notifications
- Tag-style display for assigned specializations

## 🔒 Security Features

1. **Authentication Check**: All API endpoints verify user session
2. **Authorization Check**: Role-based access control
3. **SQL Injection Protection**: Prepared statements throughout
4. **XSS Prevention**: HTML escaping in frontend
5. **CSRF Protection**: Session-based authentication
6. **Unique Constraints**: Prevent duplicate assignments

## 🐛 Troubleshooting

### Issue: "Permission denied" when research professor tries to assign
**Solution**: Check that:
1. User has `usertype = 2`
2. User has at least one accepted assignment in `team_professor_assignments`
3. Assignment status is 'accepted', not 'pending' or 'rejected'

### Issue: Tabs don't appear in dashboard
**Solution**:
1. Clear browser cache (Ctrl+Shift+R)
2. Check file permissions: All files should be readable by web server
3. Verify files are included in `dashboard/index.php`

### Issue: Cannot delete specialization
**Solution**: Specializations cannot be deleted if assigned to users/teams. First remove all assignments, then delete.

### Issue: Dropdown shows no specializations
**Solution**: Check that:
1. Specializations exist in database
2. Specializations are marked as `is_active = 1`
3. API endpoint is accessible

## 📊 Sample Data

The migration includes sample specializations:
- Artificial Intelligence (Computer Science, College of Science)
- Cybersecurity (Computer Science, College of Science)
- Software Engineering (Computer Science, College of Science)
- Construction Technology and Management (Civil Engineering, College of Engineering)
- Structural Engineering (Civil Engineering, College of Engineering)

## 🔄 Future Enhancements

Potential improvements:
- Export specialization assignments to PDF/Excel
- Bulk assignment interface
- Specialization categories/hierarchies
- Assignment history and audit logs
- Email notifications on assignment
- Specialization expiration dates

## 📞 Support

For issues or questions:
1. Check the troubleshooting section
2. Review database table structures
3. Check API error logs in browser console
4. Verify user permissions in database

## ✅ Testing Checklist

- [ ] Database tables created successfully
- [ ] Sample specializations appear in admin interface
- [ ] Admin can add new specialization
- [ ] Admin can edit existing specialization
- [ ] Admin can toggle specialization status
- [ ] Admin can delete unused specialization
- [ ] Admin can filter specializations
- [ ] Research professor sees only their assigned teams
- [ ] Research professor can assign specialization to team
- [ ] Research professor can assign specialization to team member
- [ ] Research professor can remove specialization
- [ ] Non-research-professor faculty cannot access assignment
- [ ] Students cannot access any specialization features

---

**Version**: 1.0  
**Date**: 2026-01-14  
**Author**: System
