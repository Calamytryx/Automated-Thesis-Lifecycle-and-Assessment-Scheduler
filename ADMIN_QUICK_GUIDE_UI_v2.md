# Program-Specific Manuscript Requirements - Admin Quick Guide

## What's New (Phase 8 Update)

The manuscript configuration interface has been completely redesigned for better usability:

### Old Way ❌
- 200+ checkboxes visible at once
- Overwhelming and confusing
- Hard to find specific programs
- Slow to configure

### New Way ✅
- Step-by-step workflow
- Programs filtered by type
- Quick-select buttons
- Shows specialization information
- Much faster configuration

---

## How to Use the New Interface

### Step 1: Open Manuscript Configuration

1. Go to **Dashboard** → **Requirements** tab
2. Find the requirement you want to configure (e.g., "Final Manuscript")
3. Click **Edit** or the row to open the form
4. Scroll down to find the **Program-Specific Configuration** section

### Step 2: Choose Defense Type

The first thing you'll see is:

```
Step 1: Select Defense Type
[Choose Defense Type ▼]
```

**Click the dropdown and select ONE defense type:**
- **Title Proposal** - For initial title proposal defense
- **Title Defense** - For title/proposal defense
- **Final Defense** - For final thesis/project defense
- **Re-Defense** - For teams that need to re-defend

### Step 3: Select Programs

After selecting a defense type, the programs section appears:

```
Step 2: Select Programs
[Select All] [Clear All] [Toggle]
Filter by Program Type: [▼]
```

**Quick-Select Buttons:**
- **Select All** - Check all visible programs (saves clicking 50 times!)
- **Clear All** - Uncheck all programs
- **Toggle** - Flip selections (select becomes deselect, vice versa)

### Step 4: Filter Programs (Optional)

Use the filter dropdown to narrow down programs:
- **All Programs** - Show all 55+ programs
- **Bachelor** - Show only Bachelor degree programs (~30)
- **Master** - Show only Master programs (~15)
- **Ph.D.** - Show only PhD programs (~5)
- **Law/Juris Doctor** - Show only law programs (~3)

This reduces the list significantly, making it easier to find what you need.

### Step 5: Check Programs

Checkboxes appear below the filter in a 3-column grid. Each shows:

```
☐ Bachelor of Science in Computer Science - Artificial Intelligence
☐ Bachelor of Science in Computer Science - Cybersecurity
☐ Bachelor of Science in Information Technology
```

**Just check the boxes for programs that need this manuscript for this defense type.**

### Step 6: Verify Selection

As you select programs, a summary appears:

```
ℹ️ Summary: 15 program(s) selected for Final Defense
```

This helps you verify you selected the right number before saving.

### Step 7: Save

Click the **Save** button at the bottom of the form.

- Button shows loading spinner while saving
- You'll see a success message
- Page reloads with the new configuration

---

## Common Tasks

### Task: "Final Manuscript - Only for Final Defense and CS Programs"

1. Select defense type: **Final Defense**
2. Filter by program type: **Bachelor** (assuming CS programs are Bachelors)
3. Click **Select All**
4. Manually uncheck non-CS programs (like Photography, Business Admin, etc.)
5. Or: **Clear All** and manually check only CS programs
6. Verify summary shows correct count
7. **Save**

### Task: "Mark all Master Programs for Title Defense"

1. Select defense type: **Title Defense**
2. Filter by program type: **Master**
3. Click **Select All**
4. Summary should show "~15 program(s) selected for Title Defense"
5. **Save**

### Task: "Clear a Configuration"

1. Select the defense type for which you want to clear (e.g., "Re-Defense")
2. Click **Clear All**
3. Summary disappears (no programs selected)
4. Click **Save**

Result: That defense type will have no programs configured for this manuscript.

### Task: "Fix a Configuration - Remove 5 Programs"

1. Select the defense type you want to fix
2. Current selections appear as checked
3. Uncheck the 5 programs you want to remove
4. **Save**

---

## What Each Defense Type Means

### Title Proposal
- First defense stage
- Teams defend their thesis/project title and proposal
- Usually early in the process
- Example: "Initial project proposal presentation"

### Title Defense
- Teams have already defended title proposal
- Defending the full thesis/project
- Later stage than Title Proposal
- Example: "Thesis document defense"

### Final Defense
- Last stage of defense
- Final presentation and Q&A
- Teams have already done 1-2 previous defenses
- Example: "Final thesis defense with all components"

### Re-Defense
- Teams that didn't pass and must defend again
- Can occur at any stage after initial defense
- Example: "Defense round 2" for teams that need to revise and re-submit

---

## Program Display Format

Programs now show both name AND specialization (when available):

**Format:** `Program Name - Specialization`

**Examples:**
- ✅ "Bachelor of Science in Computer Science - Artificial Intelligence" ← Has specialization
- ✅ "Bachelor of Science in Computer Science - Cybersecurity" ← Different specialization
- ✅ "Bachelor in Photography" ← No specialization available

**Why This Helps:**
- You can distinguish between similar programs
- Example: Two "BS Computer Science" programs with different specializations
- More precise configuration

---

## Tips & Tricks

### Speed Up Selection
- Use **Filter by Program Type** to narrow choices
- Use **Select All** to check all visible programs at once
- Use **Toggle** to quickly flip selections

### Avoid Mistakes
- Check the **Summary** before saving
- Example: If you expect 20 programs but summary shows 15, double-check filtering
- Remember: **Save** button is only way to persist changes

### Bulk Configuration
- Set all Bachelor programs for a defense type first
- Then deselect specific ones you don't want
- Faster than checking 50 individual boxes

### Mobile Friendly
- The interface works on phones and tablets
- 3-column grid automatically adjusts to mobile (1 column)
- All buttons and dropdowns mobile-optimized

---

## What Happens After You Save?

When you configure and save a manuscript requirement:

1. **System Stores Configuration**
   - Saves: Requirement ID + Program ID + Defense Type combinations
   - Database: `program_manuscript_requirements` table

2. **Teams See Filtered Manuscripts**
   - Teams in BS Computer Science see Final Manuscript only for Final Defense
   - Teams in other programs don't see it (if not configured for them)
   - When they're in Title Proposal stage, they don't see Final Defense manuscripts

3. **Impact on Dashboard**
   - Dashboard filters available manuscripts per team
   - Decision Support shows only applicable manuscripts
   - Home tab shows only applicable manuscripts per defense stage
   - Keeps interface clean and focused

---

## Troubleshooting

### Problem: "Programs not loading"
- Refresh page
- Check browser console (F12) for errors
- Verify database is running
- Contact system admin if persists

### Problem: "No programs appear after selecting defense type"
- The filter might be set to a specific program type with no matches
- Change filter to "-- All Programs --"
- Try a different filter option

### Problem: "I accidentally saved wrong configuration"
- Edit the requirement again
- Clear the configuration and reconfigure
- Changes saved immediately with **Save** button

### Problem: "Summary shows 0 programs but checkboxes checked"
- This shouldn't happen normally
- Try refreshing the page
- Open requirement edit again

---

## FAQ

**Q: Can I select multiple defense types at once?**
A: No - select one defense type per operation. If you need different programs for Title Defense vs Final Defense, configure one at a time.

**Q: What if a program has no specialization?**
A: That's fine - it just shows the program name without specialization. The system handles both cases.

**Q: Can I configure multiple requirements at once?**
A: No - configure one requirement at a time. The interface handles one manuscript requirement at a time.

**Q: What happens if I don't select any programs?**
A: The system will ask you to confirm - it means that manuscript won't be required for any team for that defense type.

**Q: How many programs can I select?**
A: As many as you want - the system supports all 55+ programs or a subset.

**Q: Can I see what I configured before?**
A: Yes - when you open a requirement for editing, previously selected programs appear as checked.

**Q: Does saving take long?**
A: Usually less than 1 second. The page reloads to confirm the save was successful.

---

## Example: Real Scenario

**Scenario:** "Final Manuscript is only for Final Defense AND only for CS and Engineering programs"

**Steps:**

1. Open **Requirements** → **Final Manuscript** → **Edit**

2. Scroll to **Program-Specific Configuration**

3. Select Defense Type: **Final Defense** ✓

4. Programs section appears

5. Filter by Program Type: **Bachelor** (all CS/Eng programs are Bachelors)

6. Click **Select All** ✓ (checks all visible)

7. Manually uncheck non-CS/Eng programs:
   - ☑ Bachelor of Science in Computer Science - AI → keep ✓
   - ☑ Bachelor of Science in Computer Science - Cybersecurity → keep ✓
   - ☐ Bachelor of Engineering Technology - Construction → uncheck
   - ☑ Bachelor of Science in Information Technology → keep ✓
   - ☐ Bachelor of Science in Business Administration → uncheck
   - ☐ Bachelor in Photography → uncheck

8. Summary shows: "18 program(s) selected for Final Defense"

9. Click **Save** ✓

**Result:**
- Final Manuscript now required only for CS and IT programs
- Only applies to Final Defense stage
- Other programs and defense stages unaffected

---

## Need Help?

- Check the full documentation: `PROGRAM_MANUSCRIPT_REQUIREMENTS.md`
- See implementation details: `IMPLEMENTATION_SUMMARY_PROGRAM_MANUSCRIPT_REQUIREMENTS.md`
- View system architecture: `SYSTEM_ARCHITECTURE.md`

---

**Last Updated:** November 22, 2024  
**Version:** 2.0 (Phase 8 - UI Enhancement)
