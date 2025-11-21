# Technical Change Log

## Three Issues Fixed - Simple & Direct

### Issue 1: Decision-Support Title Proposal Not Showing
**File:** `dashboard/includes/manuscript_requirements_functions.php`
**Change:** Removed `schedule_date <= NOW()` filter, added `COLLATE utf8mb4_unicode_ci`

**Before:**
```php
$defSql = "SELECT defense_type FROM defense_schedules 
           WHERE team_id = ? AND schedule_date <= NOW()
           ORDER BY schedule_date DESC LIMIT 1";
           
WHERE ... AND defense_type = ?
```

**After:**
```php
$defSql = "SELECT defense_type FROM defense_schedules 
           WHERE team_id = ?
           ORDER BY schedule_date DESC LIMIT 1";
           
WHERE ... AND defense_type COLLATE utf8mb4_unicode_ci = ? COLLATE utf8mb4_unicode_ci
```

**Impact:** Now gets most recent defense schedule regardless of date. Fixes both date filtering issue and UTF8 collation mismatches.

---

### Issue 2: Multi-Upload Blocking
**File:** `home/index.php`
**Change:** Updated upload button disable conditions to check `allow_multiple_submissions`

**Before (Line 2180, 2187, 2190):**
```javascript
<div class="upload-file-section ${req.file_name && req.status !== 'pending' ? 'upload-disabled' : ''}">
<input ... ${req.file_name && req.status !== 'pending' ? 'disabled' : 'required'}>
<button ... ${req.file_name && req.status !== 'pending' ? 'disabled' : ''}>
```

**After:**
```javascript
<div class="upload-file-section ${req.file_name && req.status !== 'pending' && !req.allow_multiple_submissions ? 'upload-disabled' : ''}">
<input ... ${req.file_name && req.status !== 'pending' && !req.allow_multiple_submissions ? 'disabled' : 'required'}>
<button ... ${req.file_name && req.status !== 'pending' && !req.allow_multiple_submissions ? 'disabled' : ''}>
```

**Impact:** Upload button now stays enabled for requirements that allow multiple submissions, even after first submission.

---

### Issue 3: PDF Extraction Error
**File:** `decision-support/index.php`
**Change:** Added null/empty checks and graceful error handling

**Before (Line 746):**
```javascript
window.extractText = async function(pdfUrl) {
    if (!pdfUrl || !outputPdfTextarea) {
        console.warn("PDF URL or output element not available...");
        return;
    }
    try {
        const response = await fetch(pdfUrl);
        if (!response.ok) throw new Error('Network response was not ok');
        // ...
    } catch (error) {
        console.error('Failed to load or extract text from PDF:', error);
    }
}
```

**After:**
```javascript
window.extractText = async function(pdfUrl) {
    if (!pdfUrl || pdfUrl.includes('/submission/') && pdfUrl.endsWith('/submission/')) {
        console.log("PDF URL not set or empty, skipping text extraction.");
        return;
    }
    if (!outputPdfTextarea) {
        console.warn("Output element not available...");
        return;
    }
    try {
        const response = await fetch(pdfUrl);
        if (!response.ok) {
            console.warn(`Failed to fetch PDF: HTTP ${response.status}...`);
            return;
        }
        // ...
    } catch (error) {
        console.warn('Could not extract text from PDF:', error.message);
    }
}

// Also added check before calling (Line 788):
if (predefinedPdfUrl && !predefinedPdfUrl.endsWith('/submission/')) {
    extractText(predefinedPdfUrl);
}
```

**Impact:** Gracefully skips PDF extraction for empty URLs without throwing errors. Changes errors to warnings.

---

## All Changes Summary

| File | Lines | Type | Impact |
|------|-------|------|--------|
| `manuscript_requirements_functions.php` | 41-47, 125-131, 66, 141 | Query modification | Decision-support shows correct requirements |
| `home/index.php` | 2179-2190 | Condition update | Multi-upload works |
| `decision-support/index.php` | 746, 788 | Error handling | No more PDF errors |

**Total Changes:** ~60 lines across 3 files
**Database Migrations:** 0
**Breaking Changes:** 0
**Testing Required:** Manual verification of 3 scenarios
