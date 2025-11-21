# WHERE TO FIND DEFENSE TYPE OVERRIDES - VISUAL GUIDE

## 🎯 QUICK LOCATION MAP

### Admin Dashboard Navigation

```
DASHBOARD SIDEBAR
└── Defense Management (Section)
    ├── Defense Schedules
    ├── Rubrics
    ├── Rubric Groups
    ├── Evaluations
    ├── Requirements
    └── ⭐ Team Overrides & Panelists ← YOU ARE HERE FOR OVERRIDES
```

---

## 📍 PLACE 1: WHERE TO OVERRIDE DEFENSE TYPE

### Location: Dashboard → "Team Overrides & Panelists" (Sidebar)

**Step 1:** Click this sidebar link
```
    Dashboard
    ├── User Management
    ├── Thesis Management
    └── Defense Management
        └── Team Overrides & Panelists ← CLICK HERE
```

**Step 2:** You see the Team Management Table
```
┌─────────────────────────────────────────────────────────┐
│ Team Name │ Defense │ Override │ Panelists │ Actions  │
├─────────────────────────────────────────────────────────┤
│ Team A    │ Proposal│ None     │ Not lock  │ O... P...│
│ Team B    │ Final   │ Title De │ 2 Locked  │ O... P...│
└─────────────────────────────────────────────────────────┘
```

**Step 3:** Click "Override" button (O...) on team row
```
Modal opens:
┌──────────────────────────────────┐
│ Set Defense Type Override        │
├──────────────────────────────────┤
│ Team Name:     [Team A]          │
│ Defense Type:  [Dropdown ▼]      │
│   → Title Proposal               │
│   → Title Defense                │
│   → Final Defense                │
│ Reason:        [Textarea...]     │
│ Expires:       [Date Picker]     │
├──────────────────────────────────┤
│ [Cancel] [Save Override]         │
└──────────────────────────────────┘
```

**Result:** Defense type is now OVERRIDDEN for this team

---

## 📍 PLACE 2: WHERE TO SEE DEFENSE TYPE IN EVALUATION

### Location: Decision-Support Page (Evaluation Page)

When an evaluator goes to evaluate a defense:
```
URL: /decision-support/index.php?schedule_id=123&group_id=45

┌─────────────────────────────────────────────────────────┐
│            Research Paper Title (Large)                  │
├─────────────────────────────────────────────────────────┤
│ [Team Name]  [Date]  [Time]  [🚩 Final Defense]         │
│                           ↑
│                           └─ NEW DEFENSE TYPE BADGE
│                              Shows which stage is being
│                              evaluated
└─────────────────────────────────────────────────────────┘
```

**Badge Colors:**
- 🔵 Blue = Title Proposal Defense
- 🔵 Dark Blue = Title Defense  
- 🟢 Green = Final Defense

---

## 📍 PLACE 3: WHERE TO SET REQUIREMENT TYPE

### Location 3A: Create Requirement

Dashboard → Requirements → "Add Requirement" button

```
┌──────────────────────────────────┐
│ Add Requirement                  │
├──────────────────────────────────┤
│ Name:          [Text...]         │
│ Description:   [Textarea...]     │
│ Defense Type:  [Dropdown] ← NEW  │
│   → Title Proposal               │
│   → Title Defense                │
│   → Final Defense                │
│   → General                      │
│ ☐ Allow Multi  [Checkbox] ← NEW  │
│ Max Subs:      [1-3] ← NEW       │
│ Template:      [File Upload]     │
│ Due Date:      [Date Picker]     │
├──────────────────────────────────┤
│ [Cancel] [Create]                │
└──────────────────────────────────┘
```

### Location 3B: Edit Requirement

Dashboard → Requirements → Edit (click meatball menu → Edit)

Same form as above, but pre-filled with current values

---

## 📍 PLACE 4: WHERE TO SEE DEFENSE TYPE IN REQUIREMENTS TABLE

### Location: Dashboard → Requirements

```
┌─────────────────────────────────────────────────┐
│ Name │ Desc │ Type* │ Multi* │ Due│ Template   │
├─────────────────────────────────────────────────┤
│ Prop │ ...  │ 🔵 TI │ 🟢 Y   │... │ [Download] │
│      │      │ Prop  │ Max:3  │    │            │
├─────────────────────────────────────────────────┤
│ Manu │ ...  │ 🟢 Fi │ ⚪ No  │... │ [Download] │
│      │      │ Final │        │    │            │
└─────────────────────────────────────────────────┘
                    ↑ NEW           ↑ NEW
                 Defense         Multi-Submit
                   Type            Support
```

---

## 🔄 DATA FLOW: How It All Connects

```
ADMIN ACTIONS
    │
    ├─ 1. Create Requirement
    │   └─ Set: Defense Type = "title_proposal"
    │   └─ Set: Allow Multiple = Yes, Max = 3
    │
    ├─ 2. Create Defense Schedule
    │   └─ System detects: Team has 3 proposals → title_defense stage
    │
    ├─ 3. Set Override (if needed)
    │   └─ Admin: "This team is special → treat as final_defense"
    │   └─ Override stored in: defense_type_overrides table
    │
    └─ 4. Lock Panelists (optional)
        └─ Admin: "Don't change panelists for title_defense"
        └─ Assignments locked in: team_panelists table
            
                    ↓↓↓
                    
SYSTEM SHOWS
    │
    ├─ Dashboard Team Management
    │   └─ Display: Current defense type (with override status)
    │
    ├─ Requirements Table
    │   └─ Display: Requirement type + Multi-submit settings
    │
    └─ Decision-Support Page
        └─ Display: Defense type badge in evaluation header
        └─ Evaluator sees: "Final Defense" badge
        └─ Evaluator applies: Final Defense rubric criteria
```

---

## ❓ COMMON QUESTIONS

**Q: Where do I override a team's defense type?**
A: Dashboard → Team Overrides & Panelists → Click team → Override button

**Q: Where will the override be visible?**
A: 
- Team Management table (shows "Override Active: final_defense")
- Decision-Support page (badge shows overridden type)
- API response from `get_team_defense_info`

**Q: Where do I lock panelists so they don't change?**
A: Dashboard → Team Overrides & Panelists → Click team → Panelists button → Lock Panelists

**Q: Where do I see if a requirement allows multiple submissions?**
A: Dashboard → Requirements table → Multi-Submit column (Yes/No with max count)

**Q: Where does the defense type appear in the evaluation?**
A: Decision-Support page header → New colored badge next to date/time

**Q: Where are these settings stored in the database?**
A:
- Defense Type Overrides: `defense_type_overrides` table
- Locked Panelists: `team_panelists` table (locked=1)
- Requirement Settings: `requirements` table (requirement_type, allow_multiple_submissions, max_submissions)
- Defense Schedule Info: `defense_schedules` table (defense_type column)

---

## ✨ EXPECTED BEHAVIOR

### Scenario 1: Set Override
1. Admin goes to Team Management
2. Finds Team X (currently detected as "title_defense")
3. Clicks "Override" button
4. Sets override to "final_defense" with reason "Medical approval"
5. **Result:** Team X now treated as final_defense everywhere

### Scenario 2: View in Evaluation
1. Evaluator opens decision-support page
2. Page header shows: Team X, Date, Time, **[🟢 Final Defense]** ← Based on override
3. Evaluator sees final_defense rubrics
4. Evaluator submits evaluation

### Scenario 3: Multi-Submission Requirement
1. Admin creates requirement "Thesis Proposal"
2. Sets Defense Type = "title_proposal"
3. Checks "Allow Multiple Submissions", sets Max = 3
4. **Result:** Teams can submit 1-3 proposals for this requirement

---

## 🎨 UI COLORS REFERENCE

```
Status Badges:
├─ Title Proposal: 🔵 Cyan (#0dcaf0)
├─ Title Defense:  🔵 Blue (#0d6efd)
├─ Final Defense:  🟢 Green (#198754)
├─ General:        ⚪ Gray (#6c757d)
├─ Multi Yes:      🟢 Green + count
└─ Multi No:       ⚪ Light Gray

Button Colors:
├─ Override: 🟡 Warning (yellow)
├─ Panelists: ℹ️ Info (light blue)
├─ Lock: 🔒 Primary (blue)
└─ Unlock: 🔓 Warning (yellow)
```

---

## 📱 RESPONSIVE DESIGN

- ✅ Works on desktop (full sidebar visible)
- ✅ Works on tablet (collapsible sidebar)
- ✅ Works on mobile (hamburger menu)

Table may become scrollable on small screens to preserve all data columns.

