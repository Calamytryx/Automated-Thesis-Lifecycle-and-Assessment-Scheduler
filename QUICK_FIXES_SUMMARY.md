# Quick Fixes Applied - Decision Support & Multi-Upload

## Issues Fixed

### 1. ✅ DECISION-SUPPORT NOT SHOWING TITLE_PROPOSAL REQUIREMENT

**Problem:**
- Teams with title_proposal status had manuscript requirements configured
- Decision-support evaluation page showed nothing

**Root Cause:**
- `getTeamApplicableManuscripts()` was querying: `WHERE team_id = ? AND schedule_date <= NOW()`
- For future schedules, this returned nothing, defaulting to 'title_proposal'
- But the actual defense_schedule might have a different status stored

**Fix Applied:**
- Changed query to get most recent defense_schedule regardless of date
- Before: `schedule_date <= NOW()` (only past dates)
- After: No date filter, just get latest schedule
- Added COLLATE clauses for defense_type comparisons to fix collation mismatch

**File Modified:**
- `/opt/lampp/htdocs/dashboard/includes/manuscript_requirements_functions.php`
  - Lines 41-47: getTeamApplicableManuscripts() - removed date check
  - Lines 125-131: isManuscriptApplicableToTeam() - removed date check
  - Added `COLLATE utf8mb4_unicode_ci` to defense_type comparisons (lines 57, 141)

---

### 2. ✅ MULTI-UPLOAD BLOCKING - CAN'T RESUBMIT FILES

**Problem:**
- Users couldn't upload new versions of files marked as "allowing multiple submissions"
- Upload button was disabled after first submission even for multi-submit requirements

**Root Cause:**
- `home/index.php` upload form had condition: `${req.file_name && req.status !== 'pending' ? 'disabled' : ''}`
- This disabled uploads for ANY submitted file, ignoring `allow_multiple_submissions` flag
- Frontend didn't check if requirement allows multiple submissions

**Fix Applied:**
- Updated disable condition to check `allow_multiple_submissions` flag
- Before: Disable if `file_name exists AND status != 'pending'`
- After: Disable if `file_name exists AND status != 'pending' AND allow_multiple_submissions = false`
- Now allows uploads for multi-submit requirements even after submission

**File Modified:**
- `/opt/lampp/htdocs/home/index.php` (lines 2175-2193)
  - Updated upload-disabled class condition
  - Updated file input disabled condition
  - Updated button disabled condition

---

### 3. ✅ PDF EXTRACTION ERROR - "NETWORK RESPONSE WAS NOT OK"

**Problem:**
- Decision-support evaluation page threw error when trying to extract text from PDF
- Error: "Failed to load or extract text from PDF: Error: Network response was not ok"

**Root Cause:**
- Script tried to fetch PDF with empty filename when no manuscript was configured
- Fetching from empty URL path: `../assets/uploads/submission/` (no filename)
- This resulted in 404 error

**Fix Applied:**
- Added proper checks for empty PDF URLs
- Added graceful error handling instead of throwing errors
- Only attempts extraction if URL is valid and contains filename
- Changed error message from error to warning with helpful context

**File Modified:**
- `/opt/lampp/htdocs/decision-support/index.php` (lines 739-787)
  - Check for empty PDF URL before fetching
  - Improved error handling - logs warnings instead of errors
  - Only calls extractText if URL is valid
  - Gracefully handles missing PDF files

---

## Testing Checklist

### Test Decision-Support Title Proposal
- [ ] Create/access team with title_proposal status
- [ ] Configure manuscript requirement for program + title_proposal
- [ ] Access decision-support evaluation
- [ ] Verify correct manuscript requirement displays

### Test Multi-Upload
- [ ] Mark requirement with `allow_multiple_submissions = 1`
- [ ] Submit first file
- [ ] Upload button should still be enabled
- [ ] Submit second file - should succeed
- [ ] Verify both files are tracked

### Test PDF Extraction
- [ ] Access decision-support for evaluation with PDF
- [ ] Should extract text without errors
- [ ] Access decision-support for evaluation without PDF
- [ ] Should gracefully skip extraction without errors

---

## Technical Details

### Files Changed
1. `/opt/lampp/htdocs/dashboard/includes/manuscript_requirements_functions.php`
   - 2 query modifications
   - 2 COLLATE clause additions

2. `/opt/lampp/htdocs/home/index.php`
   - 1 template condition update

3. `/opt/lampp/htdocs/decision-support/index.php`
   - 1 JavaScript error handling improvement

### Database Changes
- None - all changes are in application logic

### Backward Compatibility
- ✅ All changes are backward compatible
- ✅ Existing evaluations unaffected
- ✅ No migrations needed
- ✅ Graceful fallbacks for missing data

---

## Key Improvements

1. **Defense Type Matching:** Now correctly matches defense_type even for future schedules
2. **Multi-Submission Support:** Frontend now respects multi-submit flag
3. **Error Handling:** PDF extraction is more robust with graceful fallbacks
4. **Collation Consistency:** Fixed UTF8 collation mismatches in database queries

---

## Deployment Notes

- Simple deployment - just replace the 3 files
- No database migrations
- No API changes
- Can be deployed during business hours
- Immediate effect after upload
