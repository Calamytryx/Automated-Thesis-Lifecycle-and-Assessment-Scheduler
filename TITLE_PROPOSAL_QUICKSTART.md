# 🚀 Title Proposal Feature - Quick Start Guide

## What Was Added?

A **"Title Proposal"** checkbox in the team edit/add form that automatically:
- Restricts roles to **Leader** and **Member** only
- Hides the **Adviser** option
- Saves the preference to the database

## Where to Find It?

**Location:** Teams Management → Edit Team Modal  
**Look for:** Checkbox labeled "Title Proposal" with explanation

## How to Use It?

### Step 1: Open Team Edit
- Go to Dashboard → Teams
- Click the menu button (⋮) on any team
- Select "Edit"

### Step 2: Find the Checkbox
Below "Research Title" field, you'll see:
```
☑ Title Proposal - Automatically sets professor as instructor 
  (only Leader and Member roles editable)
```

### Step 3: Toggle the Setting
- **Check it:** Only Leader and Member roles available
- **Uncheck it:** All roles (Adviser, Leader, Member) available

### Step 4: Save
- Click "Save Changes" button
- Setting is saved to database

## Visual Behavior

### When CHECKED ✓
Role dropdowns show:
- ✓ Leader
- ✓ Member
- ✗ Adviser (disabled/hidden)

### When UNCHECKED 
Role dropdowns show:
- ✓ Adviser
- ✓ Leader  
- ✓ Member

## Database

**Column Added:** `title_proposal`  
**Table:** `teams`  
**Type:** TINYINT (0 or 1)  
**Default:** 0 (unchecked)

**Run this if needed:**
```sql
ALTER TABLE teams ADD COLUMN IF NOT EXISTS title_proposal TINYINT DEFAULT 0;
```

## Files Changed

1. ✅ `/dashboard/app.js.php` - Added checkbox & JavaScript handler
2. ✅ `/dashboard/includes/edit_items.php` - Saves changes
3. ✅ `/dashboard/includes/add_items.php` - Saves new teams
4. ✅ `/assets/setup/DBcreation.sql` - Updated schema

## Examples

### Example 1: Title Proposal Team
```
Team Name: "E-Commerce System"
Title Proposal: ✓ CHECKED
Available Roles: Leader, Member
Result: Only students can be leader/member, no adviser needed
```

### Example 2: Regular Team  
```
Team Name: "Mobile App Project"
Title Proposal: ☐ UNCHECKED
Available Roles: Adviser, Leader, Member
Result: Full flexibility, adviser can be assigned
```

## All Done!

✅ Feature implemented  
✅ Database ready  
✅ Code validated  
✅ Ready to use

Start by editing any team to see the new checkbox!

---

**Questions?** See the full documentation: `TITLE_PROPOSAL_FEATURE.md`
