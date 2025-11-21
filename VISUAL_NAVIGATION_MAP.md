# 🗺️ VISUAL NAVIGATION MAP

## WHERE EVERYTHING IS - AT A GLANCE

```
┌─────────────────────────────────────────────────────────────┐
│                     SYSTEM OVERVIEW                         │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ADMIN DASHBOARD                                            │
│  ═══════════════════════════════════════════════════════    │
│  ┌──────────────────────┐  ┌─────────────────────────┐    │
│  │ SIDEBAR              │  │ MAIN CONTENT AREA       │    │
│  ├──────────────────────┤  ├─────────────────────────┤    │
│  │ 📊 Overview          │  │ Tab Content Changes     │    │
│  │ 👥 Users             │  │                         │    │
│  │ 📚 Thesis Mgmt       │  │ 1️⃣ Requirements Tab     │    │
│  │ 🎓 Defense Mgmt      │  │   └─ NEW Defense Type   │    │
│  │  ├─ Schedules        │  │   └─ NEW Multi-Submit   │    │
│  │  ├─ Rubrics          │  │   └─ Form: Add/Edit     │    │
│  │  ├─ Rubric Groups    │  │      └─ Defense Type    │    │
│  │  ├─ Evaluations      │  │      └─ Multi checkbox  │    │
│  │  ├─ Requirements     │  │      └─ Max submissions │    │
│  │  └─ 🆕 Team          │  │                         │    │
│  │     Overrides &      │  │ 2️⃣ Team Overrides Tab   │    │
│  │     Panelists        │  │   └─ NEW Team table     │    │
│  │      (THIS IS NEW!)  │  │   └─ Override modal     │    │
│  │ 📁 File Mgmt         │  │   └─ Panelists modal    │    │
│  │ ⚙️ System             │  │                         │    │
│  └──────────────────────┘  └─────────────────────────┘    │
│                                                              │
│  EVALUATION PAGE (Decision Support)                         │
│  ═══════════════════════════════════════════════════════    │
│  ┌──────────────────────────────────────────────────┐     │
│  │ Research Title (Large)                           │     │
│  │ [👥 Team] [📅 Date] [🕐 Time] [🚩 Defense Type] │     │
│  │                              ↑ NEW BADGE        │     │
│  │ Team Members                                     │     │
│  │ Rubrics & Score Sheet                            │     │
│  │ PDF Viewer                                       │     │
│  └──────────────────────────────────────────────────┘     │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## 🎯 FEATURE LOCATIONS - DETAILED

### LOCATION 1️⃣: REQUIREMENTS TABLE

```
Dashboard → Defense Management → Requirements
                                      ↓
┌─────────────────────────────────────────────────────────────┐
│ Requirements Management                                      │
├─────────────────────────────────────────────────────────────┤
│ [Add Requirement] [Refresh]                                 │
├─────────────────────────────────────────────────────────────┤
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ Name  │ Desc │ Type* │ Multi* │ Due  │ Template │ Act │ │
│ │───────┼──────┼───────┼────────┼──────┼──────────┼─────│ │
│ │ Prop  │ ...  │ 🔵    │ 🟢 Y   │ ...  │ [DL]     │ ⋯ │ │
│ │       │      │ Title │ Max:3  │      │          │     │ │
│ │       │      │ Prop  │        │      │          │     │ │
│ ├───────┼──────┼───────┼────────┼──────┼──────────┼─────┤ │
│ │ Manu  │ ...  │ 🟢    │ ⚪ No  │ ...  │ [DL]     │ ⋯ │ │
│ │       │      │ Final │        │      │          │     │ │
│ │       │      │ Def   │        │      │          │     │ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                               │
│ * NEW COLUMNS - Defense Type & Multi-Submit                │
└─────────────────────────────────────────────────────────────┘

Pagination: < 1 2 3 >
```

### LOCATION 2️⃣: ADD/EDIT REQUIREMENT MODAL

```
Add Requirement Button Click
          ↓
┌──────────────────────────────────────────┐
│ 📝 Add Requirement                        │
├──────────────────────────────────────────┤
│                                           │
│ Name:                                    │
│ [_________________________]               │
│                                           │
│ Description:                             │
│ [_________________________]               │
│                                           │
│ 🆕 Defense Type:                         │
│ [Dropdown ▼]                             │
│  → Title Proposal                        │
│  → Title Defense                         │
│  → Final Defense                         │
│  → General                               │
│                                           │
│ 🆕 ☐ Allow Multiple Submissions          │
│     (Teams can submit up to 3)           │
│                                           │
│ 🆕 Maximum Submissions: [1-3] (hidden)   │
│     ↑ Only shows when checkbox checked   │
│                                           │
│ File Template:                           │
│ [Choose File] (optional)                 │
│                                           │
│ Due Date:                                │
│ [Date Picker]                            │
│                                           │
├──────────────────────────────────────────┤
│ [Cancel]  [Create/Save]                 │
└──────────────────────────────────────────┘
```

### LOCATION 3️⃣: TEAM MANAGEMENT TAB

```
Dashboard → Defense Management → Team Overrides & Panelists
                                         ↓
┌─────────────────────────────────────────────────────────────┐
│ Team Management - Defense Type Overrides & Panelist mgmt     │
├─────────────────────────────────────────────────────────────┤
│ [Search: ____________]  [🔄 Refresh]                        │
├─────────────────────────────────────────────────────────────┤
│ ┌───────────────────────────────────────────────────────┐   │
│ │Team │ Current  │ Override  │ Panelists │ Actions   │   │
│ │Name │ Type     │ Status    │ (Locked)  │           │   │
│ │─────┼──────────┼───────────┼───────────┼───────────│   │
│ │Team │ 🔵 Title │ None      │ Not Locked│ [Over...] │   │
│ │ A   │ Proposal │           │           │ [Panel...] │   │
│ │─────┼──────────┼───────────┼───────────┼───────────│   │
│ │Team │ 🟢 Final │ Active:   │ 2 Locked  │ [Over...] │   │
│ │ B   │ Defense  │ Title Def │           │ [Panel...] │   │
│ │─────┼──────────┼───────────┼───────────┼───────────│   │
│ │Team │ 🔵 Title │ None      │ Not Locked│ [Over...] │   │
│ │ C   │ Defense  │           │           │ [Panel...] │   │
│ └───────────────────────────────────────────────────────┘   │
│                                                               │
│ Pagination: < 1 2 3 >                                        │
└─────────────────────────────────────────────────────────────┘
```

### LOCATION 4️⃣: OVERRIDE MODAL

```
Click "Override" Button on Team Row
             ↓
┌──────────────────────────────────────────┐
│ ⚙️ Set Defense Type Override              │
├──────────────────────────────────────────┤
│                                           │
│ Team Name:                               │
│ [Team A] (read-only)                     │
│                                           │
│ Override Defense Type:                   │
│ [Select ▼]                               │
│  → Title Proposal                        │
│  → Title Defense                         │
│  → Final Defense                         │
│                                           │
│ Reason for Override:                     │
│ [────────────────────────────────────]   │
│ E.g., Medical leave, schedule conflict   │
│ [────────────────────────────────────]   │
│                                           │
│ Expiration Date (optional):              │
│ [Date Picker]  ← Leave blank for perm   │
│                                           │
│ ℹ️ Note: This override will take          │
│ precedence over automatic detection.     │
│ Remains active until removed or          │
│ expiration date is reached.              │
│                                           │
├──────────────────────────────────────────┤
│ [Cancel] [Save Override] [Remove Override]
│                          (if exists)
└──────────────────────────────────────────┘
```

### LOCATION 5️⃣: PANELISTS LOCK MODAL

```
Click "Panelists" Button on Team Row
              ↓
┌──────────────────────────────────────────┐
│ 🔒 Manage Panelist Locks                  │
├──────────────────────────────────────────┤
│                                           │
│ Team Name:                               │
│ [Team B] (read-only)                     │
│                                           │
│ Defense Type:                            │
│ [Select ▼]                               │
│  → Title Proposal                        │
│  → Title Defense                         │
│  → Final Defense                         │
│                                           │
│ Panelists for This Stage:                │
│ ┌────────────────────────────────────┐   │
│ │ ✓ Dr. Smith (Primary) - Locked     │   │
│ │ ✓ Prof. Johnson (Secondary) - ...  │   │
│ │ ✓ Ms. Garcia (Tertiary) - ...      │   │
│ └────────────────────────────────────┘   │
│                                           │
│ ⚠️ Lock Panelists: Once locked, these    │
│ panelists cannot be changed by the      │
│ scheduler algorithm for this stage.     │
│ Use to prevent unwanted changes.        │
│                                           │
├──────────────────────────────────────────┤
│ [Cancel] [Unlock Panelists] [Lock Pan...]
└──────────────────────────────────────────┘
```

### LOCATION 6️⃣: DECISION-SUPPORT PAGE

```
Evaluator opens: /decision-support/index.php?schedule_id=5&group_id=2
                              ↓
┌─────────────────────────────────────────────────────────────┐
│ 📊 Defense Evaluation - COECSA Thesis System                 │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│        Research Paper Title (Large H1)                       │
│                                                               │
│  [👥 Team Alpha] [📅 Mar 15,2025] [🕐 2:00-3:00PM]          │
│  [🚩 Final Defense]  ← NEW BADGE                            │
│   └─ Color: GREEN
│   └─ Shows defense stage being evaluated
│   └─ Helps evaluator apply correct rubric criteria
│                                                               │
├─────────────────────────────────────────────────────────────┤
│ 📋 Team Members                                              │
│ [👤 John Smith]  [👤 Mary Johnson]  [👤 Carlos Garcia]      │
│                                                               │
│ 📄 Research Advisor: Dr. Patricia Lee                        │
│                                                               │
├─────────────────────────────────────────────────────────────┤
│ 📑 Tabs:                                                     │
│ [Research Paper ▼] [Score Sheet] [PDF]                     │
│                                                               │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ Rubric 1: Research Quality                              │ │
│ │ ┌───────────────────────────────────────────────────┐   │ │
│ │ │ Criteria    │ Excellent │ Good │ Fair │ Score    │   │ │
│ │ │ Innovation  │   ☑       │      │      │          │   │ │
│ │ │ Relevance   │           │  ☑   │      │          │   │ │
│ │ │ Depth       │           │      │  ☑   │  Score:  │   │ │
│ │ │             │           │      │      │  15 / 20 │   │ │
│ │ └───────────────────────────────────────────────────┘   │ │
│ │ Rubric 2: Presentation                                   │ │
│ │ ... more rubrics ...                                     │ │
│ │                                                           │ │
│ │ Feedback Comments:                                       │ │
│ │ [Excellent work on the methodology section...]          │ │
│ │                                                           │ │
│ │ [Submit Evaluation]                                      │ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔗 NAVIGATION FLOW

### Flow 1: Admin Sets Override

```
1. Login as Admin
   ↓
2. Click Dashboard
   ↓
3. Scroll to "Defense Management" section in sidebar
   ↓
4. Click "Team Overrides & Panelists"
   ↓
5. Find team in table (search if needed)
   ↓
6. Click "Override" button on team row
   ↓
7. Modal appears:
   - Team name auto-filled
   - Select defense type
   - Enter reason
   - Optional: set expiration
   ↓
8. Click "Save Override"
   ↓
9. Success message shown
   ↓
10. Team table updates showing override status
```

### Flow 2: Evaluator Evaluates (With Defense Type Context)

```
1. Receive email with link:
   /decision-support/index.php?schedule_id=5&group_id=2
   ↓
2. Click link / Login
   ↓
3. Decision-Support page loads
   ↓
4. See Research Title and badges:
   [Team Name] [Date] [Time] [Final Defense] ← Defense type shown!
   ↓
5. Click "Score Sheet" tab
   ↓
6. See Final Defense rubrics (context-appropriate)
   ↓
7. Complete evaluation
   ↓
8. Submit evaluation
   ↓
9. System saves with defense_type = "final_defense"
```

### Flow 3: Admin Creates Multi-Submit Requirement

```
1. Click Dashboard → Requirements
   ↓
2. Click "Add Requirement"
   ↓
3. Modal appears with new fields:
   ├─ Defense Type selector
   ├─ Allow Multiple Submissions checkbox
   └─ Max Submissions (1-3)
   ↓
4. Fill form:
   - Name = "Thesis Proposal"
   - Defense Type = "title_proposal"
   - Check "Allow Multiple Submissions"
   - Max = 3
   ↓
5. Click "Create"
   ↓
6. Requirements table shows:
   Name: Thesis Proposal
   Type: [🔵 Title Proposal]
   Multi: [🟢 Yes (Max: 3)]
   ↓
7. Students can now submit 1-3 proposals for this requirement
```

---

## 📊 DATA RELATIONSHIPS

```
┌─────────────────────────────────────────┐
│ Team                                    │
├─────────────────────────────────────────┤
│ id, name, program, created_at           │
└──────────┬───────────────────────────────┘
           │
           ├─ (1-to-1) ─→ defense_type_overrides
           │             (overrides auto-detection)
           │
           ├─ (1-to-many) ─→ team_panelists
           │                (locked assignments)
           │
           ├─ (1-to-many) ─→ defense_schedules
           │                (scheduled defenses)
           │                └─ defense_type column (shows which stage)
           │
           └─ (1-to-many) ─→ team_requirement_files
                            (submitted files)

┌─────────────────────────────────────────┐
│ Requirement                             │
├─────────────────────────────────────────┤
│ id, name, description,                  │
│ requirement_type ← NEW                  │
│ allow_multiple_submissions ← NEW        │
│ max_submissions ← NEW                   │
└──────────┬───────────────────────────────┘
           │
           └─ (1-to-many) ─→ team_requirement_files
                            (files students submit)

Flow:
Team submits 3 proposals (multi-submit)
    ↓
Stored in: team_requirement_files (submission_number 1, 2, 3)
    ↓
System detects: 3 proposals submitted
    ↓
Determines: defense_type = "title_defense" (next stage)
    ↓
Creates: defense_schedule with defense_type = "title_defense"
    ↓
Evaluator sees: [🔵 Title Defense] badge
    ↓
Evaluator applies: Title defense rubrics
```

---

## 🎨 COLOR CODING

```
Defense Types:
┌─────────────────────────────────┐
│ 🔵 Title Proposal      (Cyan)   │
│    Used for initial proposals   │
│    Badge: Light Blue            │
│                                 │
│ 🔵 Title Defense       (Blue)   │
│    Used for approved titles     │
│    Badge: Navy Blue             │
│                                 │
│ 🟢 Final Defense       (Green)  │
│    Used for final defense       │
│    Badge: Forest Green          │
│                                 │
│ ⚪ General             (Gray)   │
│    Generic requirement          │
│    Badge: Light Gray            │
└─────────────────────────────────┘

Status:
┌─────────────────────────────────┐
│ ✓ Active / Yes        (Green)   │
│ ✗ Inactive / No       (Gray)    │
│ ⚠️ Warning State       (Yellow)  │
│ ❌ Error / Failed      (Red)     │
└─────────────────────────────────┘
```

---

## ✅ QUICK CHECKLIST

- [ ] Can I access Team Overrides & Panelists tab from sidebar?
- [ ] Does Requirements table show Defense Type column?
- [ ] Does Requirements table show Multi-Submit column?
- [ ] Can I create requirement with Defense Type selection?
- [ ] Can I enable multiple submissions on requirement?
- [ ] Can I click Override button and see modal?
- [ ] Can I set defense type override and save?
- [ ] Does override appear in Team Management table?
- [ ] Can I click Panelists button and see modal?
- [ ] Does Decision-Support page show defense type badge?
- [ ] Is badge colored according to defense type?

