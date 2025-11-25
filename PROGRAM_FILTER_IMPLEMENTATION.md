# 📊 PROGRAM FILTER SYSTEM - IMPLEMENTATION PLAN

**Objective**: Implement program-level filtering across all modules (Users, Teams, etc.) so they work consistently together

**Status**: IN PROGRESS

---

## 🎯 REQUIREMENTS

1. ✅ **Global Program Selector**
   - Dropdown at top of dashboard
   - Selects which program to view
   - Affects all modules consistently

2. ✅ **Module Filtering**
   - Users filtered by selected program
   - Teams filtered by selected program
   - Other modules respect the selection

3. ✅ **Persistent Selection**
   - Remember user's program selection
   - Store in session/localStorage
   - Apply on page reload

4. ✅ **Visual Indication**
   - Show current program selection
   - Clear indication of filtering state
   - Count of records shown

---

## 🏗️ ARCHITECTURE

### Frontend
```
┌─────────────────────────────────────────┐
│  GLOBAL PROGRAM FILTER DROPDOWN         │
│  (Top of dashboard - persistent)        │
└────────────────────┬────────────────────┘
                     │
        ┌────────────┼────────────┐
        ▼            ▼            ▼
    USERS TAB   TEAMS TAB   OTHER MODULES
    (Filtered)  (Filtered)  (Filtered)
```

### Backend
```
GET /includes/get_users.php?program=CS
GET /includes/get_teams.php?program=CS
GET /includes/get_*.php?program=CS
```

### Storage
```
JavaScript:
  - sessionStorage['selectedProgram']
  - localStorage['selectedProgram']

Server:
  - $_SESSION['selectedProgram']
```

---

## 📋 IMPLEMENTATION STEPS

### Step 1: Create Global Program Filter UI
- Add dropdown to header/navbar
- Show current selection
- Load all available programs

### Step 2: Update JavaScript
- Handle program selection change
- Reload affected tables
- Store selection in storage

### Step 3: Update Backend
- Accept `program` parameter in get_*.php files
- Filter results by program
- Return filtered data

### Step 4: Test Integration
- Test each module with program filter
- Test persistent selection
- Test multi-user scenarios

---

## 💾 DATA FLOW

```
User Selects Program
        │
        ▼
JavaScript event triggered
        │
        ├─► Store in localStorage/sessionStorage
        ├─► Update UI (highlight selection)
        └─► Trigger table reloads
                │
                ▼
        Backend receives ?program=X
                │
                ▼
        Filter results by program
                │
                ▼
        Return filtered JSON
                │
                ▼
        Update tables with new data
                │
                ▼
        Display counts & info
```

---

## 🔧 TECHNICAL DETAILS

### API Endpoints to Modify
```
GET /dashboard/includes/get_users.php
    - Accept: ?program=NAME
    - Filter: users.program = NAME

GET /dashboard/includes/get_teams.php
    - Accept: ?program=NAME
    - Filter: teams.program = NAME

GET /dashboard/includes/get_requirements.php
    - Accept: ?program=NAME
    - Filter: requirements where applicable

...and others
```

### Data Structure
```javascript
// Program selection object
{
  name: "Computer Science",
  value: "CS",
  id: 1,
  color: "#007bff" // optional
}

// Table reload signature
reloadTable(tableName, programFilter = null)
```

---

## ✨ USER EXPERIENCE

```
User sees dashboard
        │
        ▼
Notices program selector (top)
        │
        ▼
Sees: "Currently viewing: All Programs"
        │
        ▼
Clicks dropdown
        │
        ▼
Sees list:
  • All Programs
  • Computer Science
  • Information Technology
  • Computer Engineering
        │
        ▼
Selects "Computer Science"
        │
        ▼
All tables update instantly:
  • Users list: Only CS users
  • Teams list: Only CS teams
  • etc.
        │
        ▼
Page reloaded: Selection persists
```

---

## 📊 BENEFITS

1. **Better Organization**: View data by program
2. **Less Clutter**: See only relevant records
3. **Faster Navigation**: Easier to find specific data
4. **Consistent UX**: Same experience across modules
5. **Better Performance**: Smaller result sets
6. **Compliance**: Restrict view by program if needed

---

## 🚀 FILES TO MODIFY/CREATE

### New Files
1. `program_filter.php` - Filter control display
2. `program_filter.js` - JavaScript handler

### Modified Files
1. `index.php` - Add filter UI
2. `app.js.php` - Add filter logic
3. `get_users.php` - Add program parameter
4. `get_teams.php` - Add program parameter
5. Others as needed

### Updated Functions
1. `fetchAllUsers()` - Add program filter
2. `fetchAllTeams()` - Add program filter
3. etc.

---

## 🎯 SUCCESS CRITERIA

- ✅ Global program selector visible at top
- ✅ All modules respect selection
- ✅ Selection persists on reload
- ✅ Consistent filtering across modules
- ✅ Correct data displayed for selection
- ✅ No broken functionality
- ✅ Performance unaffected

---

**Next**: Begin implementation with Step 1
