# Front-End Changes Guide - Defense Type Overrides & Panelist Management

## Summary
This guide shows you where all the new defense type management features are visible on the front-end after the latest implementation.

---

## 🎯 WHERE TO SEE CHANGES

### **1. ADMIN DASHBOARD - Team Management Tab**

**Location:** Dashboard → Sidebar → "Team Overrides & Panelists" (Under Defense Management)

**What You See:**
- List of all teams with their current defense type
- Override status (Active/None)
- Panelist lock status
- Action buttons to manage overrides and panelist locks

**Features:**
- ✅ Search teams by name
- ✅ Refresh button to reload data
- ✅ Pagination for team list

**Table Columns:**
| Column | Shows |
|--------|-------|
| **Team Name** | Name of the team |
| **Current Defense Type** | Badge: Title Proposal / Title Defense / Final Defense |
| **Override Status** | Shows if override is active with the type |
| **Panelists (Locked)** | Shows number of locked panelists |
| **Actions** | Override & Panelist management buttons |

---

### **2. ADMIN DASHBOARD - Requirements Management Table**

**Location:** Dashboard → Sidebar → Requirements (Under Defense Management)

**What You See:**
Now shows **two new columns** (Defense Type, Multi-Submit):

**Table Columns:**
| Column | Shows |
|--------|-------|
| **Name** | Requirement name |
| **Description** | Requirement description |
| **Defense Type** | Badge: Title Proposal / Title Defense / Final Defense / General |
| **Multi-Submit** | Badge: Yes (Max: X) or No |
| **Due Date** | Due date |
| **Template** | Download link if exists |
| **Action** | Edit/Delete buttons |

---

### **3. ADMIN DASHBOARD - Add Requirement Form**

**Location:** Dashboard → Requirements → "Add Requirement" button

**New Fields Added:**

```
Name                    [Text input]
Description             [Textarea]
Defense Type            [Dropdown: Title Proposal / Title Defense / Final Defense / General] ← NEW
☐ Allow Multiple        [Checkbox] ← NEW
  Submissions
Maximum Submissions     [Number 1-3] ← NEW (shows only if checkbox checked)
File Template           [File upload]
Due Date                [Date picker]
```

**How It Works:**
- Select **Defense Type** to assign this requirement to a specific defense stage
- Check **"Allow Multiple Submissions"** to let teams submit multiple files (1-3)
- When checked, **Maximum Submissions** field appears to set limit

---

### **4. ADMIN DASHBOARD - Edit Requirement Form**

**Location:** Dashboard → Requirements → Edit (via meatball menu)

**Same new fields as Add form:**
- Pre-populated with current values
- Defense Type dropdown shows currently selected type
- Multi-submit checkbox shows current state
- Max submissions field only appears if multi-submit is enabled

---

### **5. ADMIN DASHBOARD - Set Defense Type Override Modal**

**Location:** Team Management Tab → "Override" button on team row

**What You See:**

```
Modal Title: "Set Defense Type Override"

Form Fields:
├── Team Name              [Read-only text]
├── Override Defense Type  [Dropdown: Title Proposal / Title Defense / Final Defense]
├── Reason for Override    [Textarea - e.g., "Medical leave", "Schedule conflict"]
├── Expiration Date        [Date picker - optional]
└── Info Box: "Note: This override will take precedence..."

Buttons:
├── Cancel
├── Save Override
└── Remove Override (if override already exists)
```

**Usage:**
1. Click team → Click "Override" button
2. Form auto-loads existing override if one exists
3. Select defense type to force
4. Enter reason for override
5. (Optional) Set expiration date
6. Click "Save Override" or "Remove Override"

---

### **6. ADMIN DASHBOARD - Manage Panelist Locks Modal**

**Location:** Team Management Tab → "Panelists" button on team row

**What You See:**

```
Modal Title: "Manage Panelist Locks"

Form Fields:
├── Team Name              [Read-only text]
├── Defense Type           [Dropdown: Title Proposal / Title Defense / Final Defense]
└── Panelists for Stage    [List of assigned panelists] ← NEW

Info Box: "Lock Panelists: Once locked, these panelists 
cannot be changed by the scheduler algorithm..."

Buttons:
├── Cancel
├── Unlock Panelists
└── Lock Panelists
```

**Usage:**
1. Click team → Click "Panelists" button
2. Select defense stage (title proposal, title defense, final defense)
3. View panelists assigned to this stage
4. Click "Lock Panelists" to prevent scheduler from changing them
5. Click "Unlock Panelists" to allow changes again

---

### **7. DECISION-SUPPORT PAGE - Defense Type Badge**

**Location:** `/decision-support/index.php?schedule_id=X&group_id=Y`

**What Changed:**
Added a new colored badge showing the defense type being evaluated

**Display:**
```
Research Title (large heading)

[Team Name Badge] [Date Badge] [Time Badge] [Defense Type Badge] ← NEW

Example:
├─ Team Name Badge:     "👥 Team Alpha"
├─ Date Badge:          "📅 Mar 15, 2025"
├─ Time Badge:          "🕐 2:00 PM - 3:00 PM"
└─ Defense Badge:       "🚩 Final Defense" (GREEN badge)
```

**Badge Colors:**
- 🔵 **Blue** = Title Proposal Defense
- 🔵 **Dark Blue** = Title Defense
- 🟢 **Green** = Final Defense

**Why It Matters:**
- Evaluators can immediately see which defense stage they're evaluating
- Helps context for applying correct rubric criteria
- Shows override status if applicable

---

## 📋 COMPLETE USER FLOW

### **For Admin - Managing Overrides:**

1. **Go to Dashboard**
   - Click "Team Overrides & Panelists" in sidebar

2. **Find Team**
   - Search by team name or browse list
   - See current defense type and override status

3. **Set Override (if needed)**
   - Click "Override" button on team row
   - Select defense type to force
   - Enter reason
   - (Optional) Set expiration date
   - Click "Save Override"

4. **Lock Panelists (optional)**
   - Click "Panelists" button on same team
   - Select defense stage
   - Click "Lock Panelists" to prevent changes

### **For Admin - Creating Requirements:**

1. **Go to Dashboard** → Requirements tab
2. **Click "Add Requirement"**
3. Fill in:
   - Name & Description
   - **Select Defense Type** (which stage this applies to)
   - **Optional:** Check "Allow Multiple Submissions" + set max (1-3)
   - Upload template
   - Set due date
4. **Save**

### **For Evaluator - Evaluating:**

1. **Go to Decision-Support page** (from email link or dashboard)
2. **See Defense Type Badge** at the top
   - Helps understand what stage they're evaluating
3. **Complete evaluation** using appropriate rubrics for that defense type
4. **Submit evaluation**

---

## 🎨 Visual Elements

### **Badge Colors & Icons:**

```
Title Proposal      → 🔵 Cyan/Light Blue    📌 Info
Title Defense       → 🔵 Dark Blue          📋 Primary
Final Defense       → 🟢 Green              ✅ Success
General             → ⚪ Gray               ⚙️ Secondary
Multi-Submit: Yes   → 🟢 Green              ✓ Enabled
Multi-Submit: No    → ⚪ Light Gray         ✗ Disabled
```

---

## 🔍 How to Access Each Feature

### **Feature: Set Defense Type Override**
- **Admin Dashboard** → Team Management → Click team row's "Override" button
- **API Endpoint:** `POST /api/admin_overrides.php?action=set_defense_type_override`

### **Feature: Lock Panelists**
- **Admin Dashboard** → Team Management → Click team row's "Panelists" button → "Lock Panelists"
- **API Endpoint:** `POST /api/admin_overrides.php?action=lock_panelists`

### **Feature: View Requirement Type**
- **Admin Dashboard** → Requirements → Table shows Defense Type column
- **Database:** `requirements.requirement_type` field

### **Feature: Multiple Submissions**
- **Admin Dashboard** → Requirements → Create/Edit → Check "Allow Multiple Submissions"
- **Student Upload:** Students see submission counter (1/3, 2/3, etc.)
- **Database:** `requirements.allow_multiple_submissions`, `requirements.max_submissions`

### **Feature: Defense Type in Evaluation**
- **Decision-Support Page** → Defense type badge shown at top
- **Database:** `defense_schedules.defense_type` field

---

## 📌 Quick Reference

| Feature | Admin Location | What It Does |
|---------|---|---|
| **Override Defense Type** | Team Management → Override btn | Force team to specific defense stage |
| **Lock Panelists** | Team Management → Panelists btn | Prevent scheduler from changing assigned panelists |
| **View Defense Type** | Team Management table | See auto-determined or overridden type |
| **Create Multi-Submit Req** | Requirements → Add | Allow teams to submit 1-3 files for requirement |
| **See Defense Type in Eval** | Decision-Support page | Badge shows which defense stage being evaluated |

---

## ✅ Testing Checklist

After deployment, verify:

- [ ] Admin can access "Team Overrides & Panelists" tab
- [ ] Can see team list with defense types
- [ ] Can click "Override" and set defense type override
- [ ] Override modal shows existing override if present
- [ ] Can remove override with "Remove Override" button
- [ ] Can click "Panelists" and see lock options
- [ ] Requirements table shows Defense Type column with badges
- [ ] Requirements table shows Multi-Submit column with badges
- [ ] Can create requirement with defense type selected
- [ ] Can enable multi-submission checkbox
- [ ] Max submissions field appears/disappears correctly
- [ ] Decision-Support page shows defense type badge
- [ ] Badge color matches defense type

---

## 🚀 Next Steps

If not yet implemented:
1. **Scheduler Integration** - Modify `run_scheduler.php` to check `getPersistentPanelists()` before assigning
2. **Evaluation Form Updates** - Display all submissions for multi-submit requirements
3. **Student UI** - Show submission progress (1/3, 2/3, etc.) when uploading

---

## 📞 Support

If features aren't showing:
1. Check database migration was applied: `SHOW TABLES LIKE 'team_panelists'`;
2. Verify columns exist: `DESCRIBE requirements;` (should show `requirement_type`, `allow_multiple_submissions`, `max_submissions`)
3. Check admin user type: User must have `usertype = 0` (admin)
4. Clear browser cache if UI not updating

