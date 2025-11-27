# Room Validation Guide - Defense Schedules

## 📋 Overview
The room validation system now uses **strict preset formats** to ensure consistency and prevent typos or variations.

## ✅ Valid Room Formats

### Defense Rooms
- `Defense Room 1`
- `Defense Room 2`
- **Note**: Case-insensitive, but must match exactly

### J Building (Floors 2-4, excluding floor 1)
- **Pattern**: `J[2-4][0-9][0-9]`
- **Examples**: 
  - ✅ `J201`, `J202`, ..., `J299`
  - ✅ `J301`, `J302`, ..., `J399`
  - ✅ `J401`, `J402`, ..., `J499`
- **Invalid**: 
  - ❌ `J101` (floor 1 excluded)
  - ❌ `J501` (only floors 2-4)

### S Building (Floors 2-4, excluding floor 1)
- **Pattern**: `S[2-4][0-9][0-9]`
- **Examples**: 
  - ✅ `S201`, `S202`, ..., `S299`
  - ✅ `S301`, `S302`, ..., `S399`
  - ✅ `S401`, `S402`, ..., `S499`
- **Invalid**: 
  - ❌ `S101` (floor 1 excluded)
  - ❌ `S501` (only floors 2-4)

### C Building (Floors 2-3, 5-11, excluding 4)
- **Pattern**: `C[2-3,5-11][0-9][0-9]`
- **Examples**: 
  - ✅ `C201`, `C202`, ..., `C299` (Floor 2)
  - ✅ `C301`, `C302`, ..., `C399` (Floor 3)
  - ✅ `C501`, `C502`, ..., `C599` (Floor 5)
  - ✅ `C601`, `C602`, ..., `C699` (Floor 6)
  - ✅ `C701`, `C801`, `C901`, `C1001`, `C1101`, `C1199` (Floors 7-11)
- **Invalid**: 
  - ❌ `C401` (Floor 4 excluded)
  - ❌ `C101` (no floor 1)
  - ❌ `C1201` (max floor 11)

### L Building (Floors 1-3)
- **Pattern**: `L[1-3][0-9][0-9]`
- **Examples**: 
  - ✅ `L101`, `L102`, ..., `L199`
  - ✅ `L201`, `L202`, ..., `L299`
  - ✅ `L301`, `L302`, ..., `L399`
- **Invalid**: `L401` (only floors 1-3)

---

## ❌ What Gets Rejected

### Invalid Format Examples
- ❌ `Defense Rom 1` (typo - should be "Room")
- ❌ `DefenseRoom1` (missing space)
- ❌ `Defense Room 3` (only 1-2 allowed)
- ❌ `J101` (J building excludes floor 1)
- ❌ `J501` (J building only has floors 2-4)
- ❌ `S101` (S building excludes floor 1)
- ❌ `S501` (S building only has floors 2-4)
- ❌ `C401` (C building excludes floor 4)
- ❌ `L401` (L building only has floors 1-3)
- ❌ `Room A` (not in preset format)
- ❌ `Lab 101` (not in preset format)

### Duplicate Detection
- ❌ `Defense Room 1, defense room 1` (duplicate, case-insensitive)
- ❌ `J101, j101` (duplicate, case-insensitive)
- ❌ `S201, S201` (obvious duplicate)

---

## 🎯 How to Use

### Option 1: Click Preset Buttons
1. Open **Defense Schedules** tab
2. Click **Scheduler Settings**
3. Click any preset button to add that room
4. Buttons are color-coded:
   - 🔵 Blue = Defense Rooms
   - ⚫ Gray = J Building
   - 🔵 Cyan = S Building
   - 🟢 Green = C Building
   - 🟡 Yellow = L Building

### Option 2: Manual Entry
Type room names separated by commas:
```
Defense Room 1, J101, J201, S101, C201, L101
```

### Validation Messages
- ✅ **Valid**: "Settings are valid. You can generate the schedule."
- ❌ **Invalid Format**: "Invalid room format: 'Room A'. Must be Defense Room [1-2], J[1-4]##, S[1-4]##, C[2-3,5-11]##, or L[1-3]##"
- ❌ **Duplicate**: "Duplicate room detected: 'j101' is the same as 'J101'"

---

## 🔧 Technical Details

### Regex Patterns
```javascript
Defense Room [12]$              // Defense rooms only 1 or 2
^J[2-4]\d{2}$                   // J building: floors 2-4 (no floor 1)
^S[2-4]\d{2}$                   // S building: floors 2-4 (no floor 1)
^C([23]|[5-9]|1[01])\d{2}$      // C building: floors 2-3, 5-11 (no 1, no 4)
^L[1-3]\d{2}$                   // L building: floors 1-3
```

### Normalization
Rooms are normalized by:
- Converting to lowercase
- Removing spaces
- This catches: `Defense Room 1` = `defense room 1` = `DEFENSE ROOM 1`

---

## 📝 Examples

### ✅ Valid Entries
```
Defense Room 1
Defense Room 2
J201, J301, J401
S201, S301, S401
C201, C301, C501, C601, C701
L101, L201, L301
Defense Room 1, J201, S201, C301, L101
```

### ❌ Invalid Entries
```
Defense Rom 1              → Wrong spelling
Room 1                     → Missing "Defense"
Defense Room 3             → Only 1-2 allowed
J101                       → Floor 1 excluded
J501                       → Only floors 2-4
S101                       → Floor 1 excluded
S501                       → Only floors 2-4
C401                       → Floor 4 excluded
L401                       → Only floors 1-3
Defense Room 1, Defense Room 1  → Duplicate
```

---

## 🎓 Benefits
1. **Consistency**: All rooms follow the same naming convention
2. **No Typos**: Strict validation prevents spelling mistakes
3. **No Duplicates**: Smart detection catches exact duplicates
4. **Easy Entry**: Preset buttons for common rooms
5. **Clear Errors**: Specific messages tell you what's wrong
