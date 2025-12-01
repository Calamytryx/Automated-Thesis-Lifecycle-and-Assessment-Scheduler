# Defense Type System - Quick Start Guide

## 🎯 What Was Added

### Feature 1: Per-Program Requirements
Specify exactly which requirements teams need for each defense type **per program**.

### Feature 2: Re-Defense Support  
Re-defense is now a valid defense type across the entire system.

---

## 📍 Where to Find New Features

### 1. Program Requirements Mapping
**Location:** Dashboard → Defense Management → **Program Requirements Mapping**

**What you can do:**
- Select a program
- Choose a defense type (Title Proposal, Title Defense, Final Defense, Re-Defense, General)
- Add/remove requirements for that combination
- Changes save instantly

### 2. Re-Defense in Team Overrides
**Location:** Dashboard → Defense Management → **Team Overrides & Panelists**

**What you can do:**
- Click [Override] button on any team
- Select "Re-Defense" from dropdown
- Add reason for re-defense
- Lock panelists specifically for re-defense stage

### 3. Re-Defense in Requirements
**Location:** Dashboard → Defense Management → **Requirements**

**What you can do:**
- When adding/editing requirements, select "Re-Defense" as the defense type
- Requirements marked as re-defense only apply to teams in re-defense status

---

## 🚀 Quick Setup (First Time)

1. **Configure Per-Program Requirements**
   - Go to Program Requirements Mapping
   - For each program, add which requirements apply to each defense type
   - This is ONE-TIME setup

2. **Test with a Team**
   - Go to Team Overrides & Panelists
   - Select any team
   - Click [Override] → select "Re-Defense"
   - Team now shows as in re-defense status

3. **Verify Requirements**
   - Check requirements tab - should show re-defense option
   - Create a re-defense requirement
   - Assign to your test team

---

## 🎨 Visual Indicators

### Defense Type Colors
- 🔵 **Blue** = Title Proposal
- 🔷 **Dark Blue** = Title Defense
- 🟢 **Green** = Final Defense
- 🟡 **Yellow** = Re-Defense (NEW!)
- ⚫ **Gray** = General

---

## ⚙️ API Quick Reference

### Get all programs
```bash
curl "http://localhost/api/program_requirements_mapping.php?action=get_programs"
```

### Get requirements for program + defense type
```bash
curl "http://localhost/api/program_requirements_mapping.php?action=get_requirements&program_id=5&defense_type=re-defense"
```

### Add requirement to defense type
```bash
curl -X POST "http://localhost/api/program_requirements_mapping.php?action=add_mapping" \
  -d "program_id=5&defense_type=re-defense&requirement_id=10&is_mandatory=1"
```

---

## ✅ Setup Checklist

- [ ] Refreshed browser after changes (Ctrl+Shift+R)
- [ ] Can see "Program Requirements Mapping" tab
- [ ] Can see "Re-Defense" option in Team Overrides
- [ ] Can see "Re-Defense" option in Requirements tab
- [ ] Added requirements to at least one defense type
- [ ] Created at least one re-defense requirement
- [ ] Tested setting a team to re-defense status

---

## 🔧 Files Modified

**Created:**
- `/dashboard/includes/program_requirements_functions.php` - Helper functions
- `/api/program_requirements_mapping.php` - API endpoints
- `/dashboard/includes/tabs/program_requirements_tab.php` - UI Tab

**Modified:**
- `/dashboard/index.php` - Added sidebar links and includes
- `/api/admin_overrides.php` - Added re-defense support
- `/dashboard/includes/tabs/team_management_tab.php` - Re-defense options
- `/dashboard/includes/tabs/requirements_tab.php` - Re-defense badge
- `/dashboard/app.js.php` - Re-defense in forms

**Database:**
- `program_requirements_mapping` table (NEW)
- `re_defense_assessments` table (NEW)
- Modified ENUM columns in 4 tables

---

## 🐛 Troubleshooting

### Tab not showing?
1. Clear browser cache (Ctrl+Shift+Delete)
2. Hard refresh (Ctrl+Shift+R)
3. Check browser console (F12)

### Re-Defense not in dropdown?
1. Check that you're admin (usertype=0)
2. Browser cache issue? Clear and refresh
3. Check network tab in F12 for API errors

### Requirements not loading?
1. Check browser console (F12) for errors
2. Verify API endpoint is accessible: `/api/program_requirements_mapping.php?action=get_programs`
3. Make sure you have a program selected

---

## 💡 Pro Tips

### Bulk Setup for Multiple Programs
1. Set up requirements for first program
2. Copy/paste the requirement IDs for other programs
3. Use bulk update endpoint to speed up setup

### Re-Defense Workflow  
1. Team fails evaluation → Use override to mark as re-defense
2. Add re-defense specific requirements (e.g., "Revised Thesis")
3. Assign new panelists for re-defense
4. Track outcome in defense schedule

### Per-Program Customization
Programs can have completely different requirement sets:
- **Program A:** Title Proposal requires 3 requirements
- **Program B:** Title Proposal requires 5 different requirements
- **Program C:** Title Proposal requires 2 requirements
- All independent via program_requirements_mapping table

---

## 📞 Support

If something doesn't work:

1. **Open browser console** (F12 → Console tab)
2. **Look for red errors**
3. **Try the troubleshooting steps above**
4. **Check the implementation summary** for detailed technical info: `/opt/lampp/htdocs/IMPLEMENTATION_DEFENSE_TYPE_SYSTEM.md`

---

**Everything is ready to use!** 🎉

Go to Dashboard and explore the new features.
