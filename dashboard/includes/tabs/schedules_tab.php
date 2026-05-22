<!-- Schedules Tab -->
<?php
require_once '../assets/setup/db.inc.php'; // Adjust path as needed

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUserName = 'Current User';
if (!empty($_SESSION['id'])) {
    try {
        $currentUserStmt = $pdo->prepare("SELECT COALESCE(NULLIF(TRIM(CONCAT(first_name, ' ', last_name)), ''), username) FROM users WHERE id = ? LIMIT 1");
        $currentUserStmt->execute([$_SESSION['id']]);
        $currentUserName = $currentUserStmt->fetchColumn() ?: $currentUserName;
    } catch (Exception $e) {
        if (!empty($_SESSION['username'])) {
            $currentUserName = (string) $_SESSION['username'];
        }
    }
}
?>
<div class="tab-pane fade" id="schedules" role="tabpanel" aria-labelledby="schedules-tab">
    <div class="container-fluid py-4 content-container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-2">Class/Teacher Schedules</h3>
                <p class="text-muted">Manage class schedules and time availability for teachers</p>
                <?php if ($_SESSION['usertype'] == 0): ?>
                <div class="mt-2">
                    <a href="#requirements" class="tab-redirect-link" onclick="document.getElementById('requirements-tab').click(); return false;">
                        <i class="bi bi-check-square-fill"></i>
                        <span>Manage Requirements and Research templates</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Conflicting Schedules -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> If you see overlapping schedules, please resolve them to avoid conflicts.

                    <br>Click on a schedule item to edit or delete it.
                    <br>Use the filters below to view schedules by program/section or instructor.

                    <?php
                    // Check for conflicting schedules
                    $conflictingSchedules = [];

                    try {
                        // Get all schedules with user information
                        $stmt = $pdo->query("
    SELECT 
        us1.id as schedule1_id,
        us1.user_id as user1_id,
        us1.class_name as class1_name,
        us1.day_of_week,
        us1.start_time as start1_time,
        us1.end_time as end1_time,
        us1.program as program1,
        us1.section as section1,
        us1.room as room1,
        u1.first_name as user1_first,
        u1.last_name as user1_last,
        us2.id as schedule2_id,
        us2.user_id as user2_id,
        us2.class_name as class2_name,
        us2.start_time as start2_time,
        us2.end_time as end2_time,
        us2.program as program2,
        us2.section as section2,
        us2.room as room2,
        u2.first_name as user2_first,
        u2.last_name as user2_last
    FROM user_schedules us1
    JOIN user_schedules us2 ON 
        us1.day_of_week = us2.day_of_week AND
        us1.id < us2.id AND
        (
            us1.start_time < us2.end_time AND us1.end_time > us2.start_time
        ) AND (
            us1.user_id = us2.user_id OR
            us1.room = us2.room OR
            (us1.program = us2.program AND us1.section = us2.section)
        )
    LEFT JOIN users u1 ON us1.user_id = u1.id
    LEFT JOIN users u2 ON us2.user_id = u2.id
    ORDER BY us1.day_of_week, us1.start_time
");

                        
                        $conflictingSchedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (!empty($conflictingSchedules)) {
                            echo '<br><div class="mt-3"><strong>Conflicting Schedules Found:</strong></div>';
                            echo '<div class="row mt-2">';
                            
                            foreach ($conflictingSchedules as $conflict) {
                                echo '<div class="col-md-6 mb-2">';
                                echo '<div class="card border-danger">';
                                echo '<div class="card-body p-2">';
                                echo '<small class="text-danger">';
                                echo '<strong>' . htmlspecialchars($conflict['day_of_week']) . '</strong><br>';
                                echo '1. ' . htmlspecialchars($conflict['class1_name']) . ' (' . date('g:i A', strtotime($conflict['start1_time'])) . '-' . date('g:i A', strtotime($conflict['end1_time'])) . ')<br>';
                                echo '&nbsp;&nbsp;&nbsp;' . htmlspecialchars($conflict['user1_first'] . ' ' . $conflict['user1_last']) . ' - ' . htmlspecialchars($conflict['program1']) . ' Sec: ' . htmlspecialchars($conflict['section1']);
                                if ($conflict['room1']) echo ' - Room: ' . htmlspecialchars($conflict['room1']);
                                echo '<br>';
                                echo '2. ' . htmlspecialchars($conflict['class2_name']) . ' (' . date('g:i A', strtotime($conflict['start2_time'])) . '-' . date('g:i A', strtotime($conflict['end2_time'])) . ')<br>';
                                echo '&nbsp;&nbsp;&nbsp;' . htmlspecialchars($conflict['user2_first'] . ' ' . $conflict['user2_last']) . ' - ' . htmlspecialchars($conflict['program2']) . ' Sec: ' . htmlspecialchars($conflict['section2']);
                                if ($conflict['room2']) echo ' - Room: ' . htmlspecialchars($conflict['room2']);
                                echo '</small>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                            
                            echo '</div>';
                        } else {
                            echo '<br><div class="mt-2 text-success"><i class="fas fa-check-circle me-1"></i>No conflicting schedules found.</div>';
                        }
                    } catch (PDOException $e) {
                        echo '<br><div class="mt-2 text-danger"><i class="fas fa-exclamation-circle me-1"></i>Error checking for conflicts: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                    ?>

                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="row">
            <div class="col-12">
                <div class="user-controls-container p-0 mt-3">
                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-12">
                            <div class="schedules-controls-group">
                                <div class="btn-group" role="group" aria-label="View type toggle">
                                    <button type="button" class="btn btn-outline view-type-btn" data-view="program">By Program/Section</button>
                                    <button type="button" class="btn btn-outline view-type-btn"
                                        data-view="instructor">By Instructor</button>
                                </div>
                                <input type="hidden" id="viewTypeSelect" value="">
                                <!-- College Filter -->
                                <select class="form-select user-control-height" id="collegeFilterSelect" style="width: 220px; display: none;">
                                    <option value="">Select College</option>
                                </select>

                                <!-- Program and Section Filters -->
                                <div class="d-flex gap-2 align-items-center" id="programSectionFilters">
                                    <!-- Program select (disabled until college selected) -->
                                    <select class="form-select user-control-height" id="programFilterSelect" style="width: 200px; display: none;" disabled>
                                        <option value="">Select Program</option>
                                    </select>
                                    <select class="form-select user-control-height" id="sectionFilterSelect" style="width: 180px; display: none;"
                                        disabled>
                                        <option value="">Select Section</option>
                                    </select>
                                </div>
                                <!-- Instructor Filter -->
                                <div class="d-flex gap-2 align-items-center" id="instructorFilters">
                                    <select class="form-select user-control-height" id="instructorFilterSelect" style="width: 220px; display: none;">
                                        <option value="">Select Instructors</option>
                                    </select>
                                </div>
                                <button class="btn feature-btn add-btn" data-table="user_schedules">
                                    <i class="fas fa-plus me-2"></i>Add Schedule
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2 align-items-center mb-3">
                        <div class="col-12">
                            <div class="schedule-export-row">
                                <div class="schedule-export-controls">
                                    <div class="schedule-export-menu" id="scheduleExportMenu">
                                        <button type="button" class="btn schedule-export-trigger" id="exportSchedulesPdfBtn" aria-haspopup="true" aria-expanded="false">
                                            <svg class="schedule-export-icon" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                                                <rect x="1" y="1" width="14" height="14" rx="2" stroke="currentColor" stroke-width="1.2" fill="none"></rect>
                                                <path d="M4.5 8.5L8 5l3.5 3.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" fill="none"></path>
                                                <path d="M8 5v6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" fill="none"></path>
                                            </svg>
                                            <span class="schedule-export-trigger-label">Export Table View</span>
                                            <i class="fas fa-angle-down ms-1"></i>
                                        </button>
                                        <div class="schedule-export-options" role="menu" aria-label="Schedule export options">
                                            <button type="button" class="schedule-export-option" data-export-type="table" role="menuitem">
                                                Table View
                                            </button>
                                            <button type="button" class="schedule-export-option" data-export-type="calendar" role="menuitem">
                                                Calendar View
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const viewTypeButtons = document.querySelectorAll('.view-type-btn');
                        const viewTypeInput = document.getElementById('viewTypeSelect');
                        const collegeSelect = document.getElementById("collegeFilterSelect");
                        const programSelect = document.getElementById("programFilterSelect");
                        const sectionSelect = document.getElementById("sectionFilterSelect");
                        const instructorSelect = document.getElementById("instructorFilterSelect");
                        const exportMenu = document.getElementById('scheduleExportMenu');
                        const exportTrigger = document.getElementById('exportSchedulesPdfBtn');
                        const exportOptionButtons = document.querySelectorAll('.schedule-export-option');
                        const exportTriggerLabel = document.querySelector('.schedule-export-trigger-label');
                        const currentUserName = <?php echo json_encode($currentUserName, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

                        const scheduleDayOrder = {
                            monday: 1,
                            tuesday: 2,
                            wednesday: 3,
                            thursday: 4,
                            friday: 5,
                            saturday: 6,
                            sunday: 7
                        };

                        const getSelectedText = (selectEl) => {
                            if (!selectEl || !selectEl.selectedOptions || !selectEl.selectedOptions.length) {
                                return '';
                            }
                            return selectEl.selectedOptions[0].textContent.trim();
                        };

                        const formatDatePrinted = () => {
                            const now = new Date();
                            return now.toLocaleString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric',
                                hour: 'numeric',
                                minute: '2-digit'
                            });
                        };

                        const formatTimeForPdf = (timeStr) => {
                            if (!timeStr) return '';
                            const parts = String(timeStr).split(':');
                            if (parts.length < 2) return String(timeStr);

                            let hours = parseInt(parts[0], 10);
                            const minutes = parts[1];
                            if (Number.isNaN(hours)) return String(timeStr);

                            const period = hours >= 12 ? 'PM' : 'AM';
                            hours = hours % 12 || 12;
                            return `${hours}:${minutes} ${period}`;
                        };

                        const loadJsPdf = (callback) => {
                            if (window.jspdf) {
                                callback();
                                return;
                            }

                            const script = document.createElement('script');
                            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
                            script.onload = callback;
                            script.onerror = function() {
                                if (typeof showScheduleExportToast === 'function') {
                                    showScheduleExportToast('Failed to load the PDF library.', 'error');
                                }
                            };
                            document.head.appendChild(script);
                        };

                        const drawPdfHeader = (pdf, collegeName) => {
                            const pageWidth = pdf.internal.pageSize.getWidth();
                            const centerX = pageWidth / 2;

                            pdf.setFont('times', 'bold');
                            pdf.setFontSize(14);
                            pdf.text('LYCEUM OF THE PHILIPPINES UNIVERSITY - CAVITE', centerX, 12, { align: 'center' });

                            pdf.setFont('times', 'normal');
                            pdf.setFontSize(11);
                            pdf.text(collegeName || '', centerX, 18, { align: 'center' });

                            return 28;
                        };

                        const drawContextLine = (pdf, text, y) => {
                            if (!text) {
                                return y;
                            }

                            const margin = 10;
                            pdf.setFont('times', 'bold');
                            pdf.setFontSize(11);
                            // Left-align the program/section/faculty context and keep it
                            // visually closer to the table/calendar header.
                            pdf.text(text, margin, y, { align: 'left' });
                            return y + 2; // reduced gap so it sits nearer the content below
                        };

                        const drawPdfFooter = (pdf) => {
                            const pageWidth = pdf.internal.pageSize.getWidth();
                            const pageHeight = pdf.internal.pageSize.getHeight();

                            pdf.setTextColor(0, 0, 0);
                            pdf.setFont('times', 'italic');
                            pdf.setFontSize(8.5);
                            pdf.text(`Date printed/exported: ${formatDatePrinted()}`, 10, pageHeight - 10);
                            pdf.text(`Printed by: ${currentUserName} through ATLAS`, pageWidth - 10, pageHeight - 10, { align: 'right' });
                        };

                        const sortScheduleRows = (rows) => {
                            return [...rows].sort((a, b) => {
                                const dayA = scheduleDayOrder[String(a.day_of_week || '').trim().toLowerCase()] || 99;
                                const dayB = scheduleDayOrder[String(b.day_of_week || '').trim().toLowerCase()] || 99;
                                if (dayA !== dayB) return dayA - dayB;

                                const timeA = String(a.start_time || '');
                                const timeB = String(b.start_time || '');
                                if (timeA !== timeB) return timeA.localeCompare(timeB);

                                return String(a.class_name || '').localeCompare(String(b.class_name || ''));
                            });
                        };

                        const timeToMinutes = (timeStr) => {
                            if (!timeStr) return null;
                            const parts = String(timeStr).split(':').map(Number);
                            if (parts.length < 2 || parts.some(Number.isNaN)) return null;
                            return (parts[0] * 60) + parts[1];
                        };

                        const formatCalendarTimeLabel = (minutes) => {
                            const hours24 = Math.floor(minutes / 60);
                            const mins = minutes % 60;
                            const period = hours24 >= 12 ? 'PM' : 'AM';
                            const hour12 = hours24 % 12 || 12;
                            return `${hour12}:${String(mins).padStart(2, '0')} ${period}`;
                        };

                        const getExportContext = () => {
                            const viewType = viewTypeInput.value;
                            return {
                                viewType,
                                collegeText: getSelectedText(collegeSelect),
                                programText: getSelectedText(programSelect),
                                sectionText: getSelectedText(sectionSelect),
                                instructorText: getSelectedText(instructorSelect)
                            };
                        };

                        const buildCalendarPage = (pdf, rows, context) => {
                            const dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            const pageWidth = pdf.internal.pageSize.getWidth();
                            const pageHeight = pdf.internal.pageSize.getHeight();
                            const margin = 10;
                            const bottomLimit = pageHeight - 12;
                            const timeColumnWidth = 24;
                            const usableWidth = pageWidth - (margin * 2);
                            const dayColumnWidth = (usableWidth - timeColumnWidth) / dayNames.length;
                            const slotMinutes = 30;
                            const startMinutes = 7 * 60;
                            const endMinutes = 21 * 60;
                            const rowHeight = 5.5;
                            const timeSlots = [];

                            for (let minutes = startMinutes; minutes < endMinutes; minutes += slotMinutes) {
                                timeSlots.push(minutes);
                            }

                            let y = drawPdfHeader(pdf, context.collegeText);
                            const contextLine = context.viewType === 'instructor'
                                ? (context.instructorText || '')
                                : [context.programText || '', context.sectionText ? `Section: ${context.sectionText}` : '']
                                    .filter(Boolean)
                                    .join(' | ');
                            y = drawContextLine(pdf, contextLine, y) + 2;

                            pdf.setFont('times', 'bold');
                            pdf.setFontSize(9);

                            const headerHeight = 7;
                            pdf.rect(margin, y, timeColumnWidth, headerHeight);
                            pdf.text('Time', margin + 1, y + 4.7);

                            dayNames.forEach((dayName, index) => {
                                const x = margin + timeColumnWidth + (dayColumnWidth * index);
                                pdf.rect(x, y, dayColumnWidth, headerHeight);
                                pdf.text(dayName, x + 1, y + 4.7);
                            });

                            y += headerHeight;

                            pdf.setFont('times', 'normal');
                            pdf.setFontSize(8.3);

                            timeSlots.forEach((minutes, rowIndex) => {
                                const rowTop = y + (rowIndex * rowHeight);
                                pdf.rect(margin, rowTop, timeColumnWidth, rowHeight);
                                pdf.text(formatCalendarTimeLabel(minutes), margin + 1, rowTop + 3.7);

                                dayNames.forEach((_, index) => {
                                    const x = margin + timeColumnWidth + (dayColumnWidth * index);
                                    pdf.rect(x, rowTop, dayColumnWidth, rowHeight);
                                });
                            });

                            rows.forEach((row) => {
                                const dayName = String(row.day_of_week || '').trim().toLowerCase();
                                const dayIndex = scheduleDayOrder[dayName] ? scheduleDayOrder[dayName] - 1 : -1;
                                const startMinutesValue = timeToMinutes(row.start_time);
                                const endMinutesValue = timeToMinutes(row.end_time);

                                if (dayIndex < 0 || startMinutesValue === null || endMinutesValue === null || endMinutesValue <= startMinutesValue) {
                                    return;
                                }

                                const startRowIndex = Math.max(0, Math.floor((startMinutesValue - startMinutes) / slotMinutes));
                                const endRowIndex = Math.min(timeSlots.length - 1, Math.ceil((endMinutesValue - startMinutes) / slotMinutes) - 1);

                                if (endRowIndex < startRowIndex) {
                                    return;
                                }

                                    const blockX = margin + timeColumnWidth + (dayColumnWidth * dayIndex);
                                    const blockY = y + (startRowIndex * rowHeight);
                                    const blockWidth = dayColumnWidth;
                                    const blockHeight = ((endRowIndex - startRowIndex + 1) * rowHeight);

                                    const blockInset = 0.8; // mm
                                    const innerX = blockX + blockInset;
                                    const innerY = blockY + blockInset;
                                    const innerWidth = Math.max(0, blockWidth - (blockInset * 2));
                                    const innerHeight = Math.max(0, blockHeight - (blockInset * 2));

                                    pdf.setFillColor(219, 242, 221);
                                    pdf.setDrawColor(116, 163, 118);
  
                                    if (innerWidth > 0 && innerHeight > 0) {
                                        pdf.rect(innerX, innerY, innerWidth, innerHeight, 'FD');
                                    }

                                    const blockTitle = row.class_name || 'Schedule';
                                    const blockSubtitle = [
                                        row.room ? `Room: ${row.room}` : '',
                                        row.first_name || row.last_name ? [row.first_name, row.last_name].filter(Boolean).join(' ') : '',
                                        row.section ? `Sec: ${row.section}` : ''
                                    ].filter(Boolean).join(' | ');

                                    pdf.setTextColor(41, 82, 42);
                                    pdf.setFont('times', 'bold');
                                    pdf.setFontSize(7.2);
                                    if (innerWidth > 2 && innerHeight > 3) {
                                        pdf.text(pdf.splitTextToSize(blockTitle, innerWidth - 2), innerX + 1, innerY + 3.1);

                                        if (blockSubtitle) {
                                            pdf.setFont('times', 'normal');
                                            pdf.setFontSize(6.6);
                                            pdf.text(pdf.splitTextToSize(blockSubtitle, innerWidth - 2), innerX + 1, innerY + 6.0);
                                        }
                                    }
                            });

                            pdf.setTextColor(0, 0, 0);

                            const totalPages = pdf.getNumberOfPages();
                            for (let pageIndex = 1; pageIndex <= totalPages; pageIndex++) {
                                pdf.setPage(pageIndex);
                                    drawPdfFooter(pdf);
                            }

                            const filenameParts = [
                                'schedules',
                                'calendar',
                                context.collegeText || 'college'
                            ];

                            if (context.viewType === 'program') {
                                filenameParts.push(context.programText || 'program', context.sectionText || 'section');
                            } else {
                                filenameParts.push(context.instructorText || 'faculty');
                            }

                            const filename = filenameParts
                                .map(part => String(part).toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, ''))
                                .filter(Boolean)
                                .join('_') + '.pdf';

                            pdf.save(filename || 'schedules_calendar.pdf');
                        };

                        const generateSchedulePdf = (rows) => {
                            loadJsPdf(() => {
                                const { jsPDF } = window.jspdf;
                                const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
                                const pageHeight = pdf.internal.pageSize.getHeight();
                                const margin = 10;
                                const bottomLimit = pageHeight - 18;
                                const rowPadding = 1.5;
                                const lineHeight = 4.5;
                                const columnWidths = {
                                    day: 24,
                                    time: 30,
                                    className: 62,
                                    room: 24,
                                    instructor: 54,
                                    section: 50
                                };
                                const columnX = {
                                    day: margin,
                                    time: margin + columnWidths.day,
                                    className: margin + columnWidths.day + columnWidths.time,
                                    room: margin + columnWidths.day + columnWidths.time + columnWidths.className,
                                    instructor: margin + columnWidths.day + columnWidths.time + columnWidths.className + columnWidths.room,
                                    section: margin + columnWidths.day + columnWidths.time + columnWidths.className + columnWidths.room + columnWidths.instructor
                                };

                                const viewType = viewTypeInput.value;
                                const collegeText = getSelectedText(collegeSelect);
                                const programText = getSelectedText(programSelect);
                                const sectionText = getSelectedText(sectionSelect);
                                const instructorText = getSelectedText(instructorSelect);

                                const sortedRows = sortScheduleRows(rows);
                                let y = drawPdfHeader(pdf, collegeText);
                                const contextLine = viewType === 'instructor'
                                    ? (instructorText || '')
                                    : [programText || '', sectionText ? `Section: ${sectionText}` : '']
                                        .filter(Boolean)
                                        .join(' | ');
                                y = drawContextLine(pdf, contextLine, y) + 2;

                                const drawTableHeader = () => {
                                    pdf.setFont('times', 'bold');
                                    pdf.setFontSize(9);
                                    const headerHeight = 7;

                                    pdf.rect(columnX.day, y, columnWidths.day, headerHeight);
                                    pdf.rect(columnX.time, y, columnWidths.time, headerHeight);
                                    pdf.rect(columnX.className, y, columnWidths.className, headerHeight);
                                    pdf.rect(columnX.room, y, columnWidths.room, headerHeight);
                                    pdf.rect(columnX.instructor, y, columnWidths.instructor, headerHeight);
                                    pdf.rect(columnX.section, y, columnWidths.section, headerHeight);

                                    const textY = y + 4.8;
                                    pdf.text('Day', columnX.day + columnWidths.day / 2, textY, { align: 'center' });
                                    pdf.text('Time', columnX.time + columnWidths.time / 2, textY, { align: 'center' });
                                    pdf.text('Class', columnX.className + columnWidths.className / 2, textY, { align: 'center' });
                                    pdf.text('Room', columnX.room + columnWidths.room / 2, textY, { align: 'center' });
                                    pdf.text('Instructor', columnX.instructor + columnWidths.instructor / 2, textY, { align: 'center' });
                                    pdf.text('Program / Section', columnX.section + columnWidths.section / 2, textY, { align: 'center' });

                                    y += headerHeight;
                                };

                                const drawNewPage = () => {
                                    pdf.addPage();
                                    y = drawPdfHeader(pdf, collegeText);
                                    y = drawContextLine(pdf, contextLine, y) + 2;
                                    drawTableHeader();
                                };

                                drawTableHeader();

                                sortedRows.forEach((row) => {
                                    const dayText = row.day_of_week || '';
                                    const timeText = `${formatTimeForPdf(row.start_time)} - ${formatTimeForPdf(row.end_time)}`.trim();
                                    const classText = row.class_name || 'N/A';
                                    const roomText = row.room || 'N/A';
                                    const instructorName = [row.first_name, row.last_name].filter(Boolean).join(' ').trim() || 'N/A';
                                    const programSectionText = [row.program_name, row.specialization ? `- ${row.specialization}` : '', row.section ? `Section: ${row.section}` : '']
                                        .filter(Boolean)
                                        .join(' ')
                                        .replace(/\s+/g, ' ')
                                        .trim() || 'N/A';

                                    const splitDay = pdf.splitTextToSize(dayText, columnWidths.day - rowPadding * 2);
                                    const splitTime = pdf.splitTextToSize(timeText, columnWidths.time - rowPadding * 2);
                                    const splitClass = pdf.splitTextToSize(classText, columnWidths.className - rowPadding * 2);
                                    const splitRoom = pdf.splitTextToSize(roomText, columnWidths.room - rowPadding * 2);
                                    const splitInstructor = pdf.splitTextToSize(instructorName, columnWidths.instructor - rowPadding * 2);
                                    const splitProgramSection = pdf.splitTextToSize(programSectionText, columnWidths.section - rowPadding * 2);

                                    const rowHeight = Math.max(
                                        8,
                                        splitDay.length,
                                        splitTime.length,
                                        splitClass.length,
                                        splitRoom.length,
                                        splitInstructor.length,
                                        splitProgramSection.length
                                    ) * lineHeight + 1;

                                    if ((y + rowHeight) > bottomLimit) {
                                        drawNewPage();
                                    }

                                    pdf.setFont('times', 'normal');
                                    pdf.setFontSize(8.5);
                                    const textY = y + 4.5;

                                    pdf.rect(columnX.day, y, columnWidths.day, rowHeight);
                                    pdf.rect(columnX.time, y, columnWidths.time, rowHeight);
                                    pdf.rect(columnX.className, y, columnWidths.className, rowHeight);
                                    pdf.rect(columnX.room, y, columnWidths.room, rowHeight);
                                    pdf.rect(columnX.instructor, y, columnWidths.instructor, rowHeight);
                                    pdf.rect(columnX.section, y, columnWidths.section, rowHeight);

                                    pdf.text(splitDay, columnX.day + columnWidths.day / 2, textY, { align: 'center' });
                                    pdf.text(splitTime, columnX.time + columnWidths.time / 2, textY, { align: 'center' });
                                    pdf.text(splitClass, columnX.className + columnWidths.className / 2, textY, { align: 'center' });
                                    pdf.text(splitRoom, columnX.room + columnWidths.room / 2, textY, { align: 'center' });
                                    pdf.text(splitInstructor, columnX.instructor + columnWidths.instructor / 2, textY, { align: 'center' });
                                    pdf.text(splitProgramSection, columnX.section + columnWidths.section / 2, textY, { align: 'center' });

                                    y += rowHeight;
                                });

                                const totalPages = pdf.getNumberOfPages();
                                for (let i = 1; i <= totalPages; i++) {
                                    pdf.setPage(i);
                                    drawPdfFooter(pdf);
                                }

                                const filenameParts = [
                                    'schedules',
                                    viewType,
                                    collegeText || 'college'
                                ];
                                if (viewType === 'program') {
                                    filenameParts.push(programText || 'program', sectionText || 'section');
                                } else {
                                    filenameParts.push(instructorText || 'faculty');
                                }

                                const filename = filenameParts
                                    .map(part => String(part).toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, ''))
                                    .filter(Boolean)
                                    .join('_') + '.pdf';

                                pdf.save(filename || 'schedules_report.pdf');
                            });
                        };

                        const generateCalendarSchedulePdf = (rows) => {
                            loadJsPdf(() => {
                                const { jsPDF } = window.jspdf;
                                const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
                                buildCalendarPage(pdf, rows, getExportContext());
                            });
                        };

                        const setExportMenuOpen = (isOpen) => {
                            if (!exportMenu || !exportTrigger) return;
                            exportMenu.classList.toggle('is-open', isOpen);
                            exportTrigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                        };

                        const updateExportTriggerLabel = () => {
                            if (!exportTriggerLabel) return;
                            exportTriggerLabel.textContent = viewTypeInput.value === 'instructor'
                                ? 'Export Faculty View'
                                : 'Export Program / Section View';
                        };

                        const setExportBusy = (isBusy) => {
                            if (!exportTrigger) return;
                            exportTrigger.disabled = isBusy;
                            if (exportTriggerLabel) {
                                exportTriggerLabel.textContent = isBusy
                                    ? 'Preparing...'
                                    : (viewTypeInput.value === 'instructor' ? 'Export Faculty View' : 'Export Program / Section View');
                            }
                        };

                        const showScheduleExportToast = (message, type = 'warning') => {
                            if (typeof showToast === 'function') {
                                const toastTitle = type === 'success' ? 'Success' : type === 'warning' ? 'Notice' : 'Error';
                                const toastType = type === 'warning' ? 'notice' : (type === 'danger' ? 'error' : type);
                                showToast(toastTitle, message, toastType);
                                return;
                            }

                            const containerId = 'scheduleExportToastContainer';
                            let container = document.getElementById(containerId);
                            if (!container) {
                                container = document.createElement('div');
                                container.id = containerId;
                                container.className = 'position-fixed top-0 end-0 p-3';
                                container.style.zIndex = '9999';
                                document.body.appendChild(container);
                            }

                            const toastId = `schedule-export-toast-${Date.now()}`;
                            const toastClass = type === 'success' ? 'text-bg-success' : (type === 'error' || type === 'danger' ? 'text-bg-danger' : 'text-bg-warning');
                            const toastHtml = `
                                <div id="${toastId}" class="toast border-0 shadow-lg ${toastClass}" role="alert" aria-live="assertive" aria-atomic="true">
                                    <div class="d-flex align-items-start p-3">
                                        <div class="toast-body p-0">${message}</div>
                                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                                    </div>
                                </div>
                            `;

                            container.insertAdjacentHTML('beforeend', toastHtml);
                            const toastElement = new bootstrap.Toast(document.getElementById(toastId), {
                                autohide: true,
                                delay: 5000
                            });
                            toastElement.show();
                            document.getElementById(toastId).addEventListener('hidden.bs.toast', function() {
                                this.remove();
                            });
                        };

                        const fetchScheduleReport = (exportType) => {
                            const context = getExportContext();
                            const college = collegeSelect.value;

                            if (!context.viewType) {
                                showScheduleExportToast('Please select a report view first.', 'warning');
                                return;
                            }

                            if (!college) {
                                showScheduleExportToast('Please select a college first.', 'warning');
                                return;
                            }

                            if (!['table', 'calendar'].includes(exportType)) {
                                showScheduleExportToast('Unsupported export type.', 'warning');
                                return;
                            }

                            const payload = {
                                view_type: context.viewType,
                                college: college,
                                export_type: exportType
                            };

                            console.log('[Schedules Export] start', { exportType, viewType: context.viewType, college });

                            if (context.viewType === 'program') {
                                payload.program = programSelect.value;
                                payload.section = sectionSelect.value;

                                if (!payload.program || !payload.section) {
                                    showScheduleExportToast('Please select both program and section before exporting.', 'warning');
                                    return;
                                }
                            } else if (context.viewType === 'instructor') {
                                payload.instructor = instructorSelect.value;

                                if (!payload.instructor) {
                                    showScheduleExportToast('Please select an instructor before exporting.', 'warning');
                                    return;
                                }
                            }

                            setExportBusy(true);
                            setExportMenuOpen(false);

                            fetch('includes/get_schedules_report.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(payload)
                            })
                                .then(response => response.json())
                                .then(result => {
                                    if (!result || !result.success) {
                                        throw new Error(result && result.message ? result.message : 'Failed to generate schedule report.');
                                    }

                                    const rows = Array.isArray(result.data) ? result.data : [];
                                    if (!rows.length) {
                                        showScheduleExportToast('No schedules found for the selected filters.', 'warning');
                                        return;
                                    }

                                    if (exportType === 'calendar') {
                                        generateCalendarSchedulePdf(rows);
                                        return;
                                    }

                                    generateSchedulePdf(rows);
                                })
                                .catch(error => {
                                    console.error('Schedule export error:', error);
                                    showScheduleExportToast(error.message || 'Failed to generate schedule report.', 'error');
                                })
                                .finally(() => {
                                    console.log('[Schedules Export] finish', { exportType, viewType: context.viewType });
                                    setExportBusy(false);
                                });
                        };

                        if (exportTrigger) {
                            exportTrigger.addEventListener('click', function(e) {
                                e.preventDefault();
                                setExportMenuOpen(!exportMenu?.classList.contains('is-open'));
                            });
                        }

                        exportOptionButtons.forEach((button) => {
                            button.addEventListener('click', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                fetchScheduleReport(this.dataset.exportType || 'table');
                            });
                        });

                        document.addEventListener('click', function(e) {
                            if (exportMenu && !exportMenu.contains(e.target)) {
                                setExportMenuOpen(false);
                            }
                        });

                        if (exportMenu) {
                            exportMenu.addEventListener('mouseleave', function() {
                                setExportMenuOpen(false);
                            });
                        }

                        updateExportTriggerLabel();

                        const loadInstructorsForCollege = (college = '') => {
                            instructorSelect.innerHTML = '<option value="">Select Instructors</option>';

                            const url = college ?
                                `includes/tabs/load_instructors.php?college=${encodeURIComponent(college)}` :
                                'includes/tabs/load_instructors.php';

                            return fetch(url)
                                .then(res => res.text())
                                .then(html => {
                                    instructorSelect.innerHTML = '<option value="">Select Instructors</option>' + html;
                                })
                                .catch(err => {
                                    console.error('Error loading instructors:', err);
                                    alert('Failed to load instructors.');
                                });
                        };
                        window.loadInstructorsForCollege = loadInstructorsForCollege;

                        function updateFilterVisibility() {
                            const selectedView = viewTypeInput.value;
                            const showCollege = selectedView === 'program' || selectedView === 'instructor';

                            collegeSelect.style.display = showCollege ? 'inline-block' : 'none';

                            if (selectedView === 'program') {
                                programSelect.style.display = 'inline-block';
                                sectionSelect.style.display = 'inline-block';
                                instructorSelect.style.display = 'none';
                            } else if (selectedView === 'instructor') {
                                programSelect.style.display = 'none';
                                sectionSelect.style.display = 'none';
                                instructorSelect.style.display = 'inline-block';
                            } else {
                                programSelect.style.display = 'none';
                                sectionSelect.style.display = 'none';
                                instructorSelect.style.display = 'none';
                            }
                        }

                        // 🔘 View type button logic
                        viewTypeButtons.forEach(button => {
                            button.addEventListener('click', function() {
                                // Update active class
                                viewTypeButtons.forEach(btn => btn.classList.remove('active'));
                                this.classList.add('active');

                                // Update hidden input + trigger visibility
                                const newView = this.getAttribute('data-view');
                                viewTypeInput.value = newView;
                                updateFilterVisibility();
                            });
                        });

                        // 🔁 Program select triggers section update
                        programSelect.addEventListener("change", function() {
                            const programId = this.value;
                            sectionSelect.innerHTML = '<option value="">Select Section</option>';
                            sectionSelect.disabled = true;

                            if (programId) {
                                fetch(
                                        `includes/tabs/get_section.php?program_id=${encodeURIComponent(programId)}`)
                                    .then(res => {
                                        if (!res.ok) throw new Error('Network response was not ok');
                                        return res.json();
                                    })
                                    .then(data => {
                                        if (Array.isArray(data) && data.length > 0) {
                                            data.forEach(section => {
                                                const option = document.createElement(
                                                    "option");
                                                option.value = section;
                                                option.textContent = section;
                                                sectionSelect.appendChild(option);
                                            });
                                            sectionSelect.disabled = false;
                                        }
                                    })
                                    .catch(err => {
                                        console.error("Error fetching sections:", err.message);
                                        alert("Failed to load sections.");
                                    });
                            }
                        });

                        // 🔁 College select triggers program update
                        collegeSelect.addEventListener('change', function() {
                            const college = this.value;
                            // Reset program and section
                            programSelect.innerHTML = '<option value="">Select Program</option>';
                            programSelect.disabled = true;
                            sectionSelect.innerHTML = '<option value="">Select Section</option>';
                            sectionSelect.disabled = true;
                            instructorSelect.value = '';

                            if (viewTypeInput.value === 'program' && college) {
                                fetch(`includes/tabs/load_programs.php?college=${encodeURIComponent(college)}`)
                                    .then(res => {
                                        if (!res.ok) throw new Error('Network response was not ok');
                                        return res.text();
                                    })
                                    .then(html => {
                                        // html contains <option> tags
                                        programSelect.innerHTML = '<option value="">Select Program</option>' + html;
                                        programSelect.disabled = false;
                                    })
                                    .catch(err => {
                                        console.error('Error loading programs for college:', err);
                                        alert('Failed to load programs for selected college.');
                                    });
                            }

                            if (viewTypeInput.value === 'instructor') {
                                loadInstructorsForCollege(college);
                            }

                            // Clear board until user picks program & section
                            loadSchedules();
                        });

                        // Populate colleges on load
                        fetch('includes/tabs/load_colleges.php')
                            .then(res => res.text())
                            .then(html => {
                                collegeSelect.innerHTML = '<option value="">Select College</option>' + html;
                            })
                            .catch(err => console.error('Failed to load colleges:', err));

                        loadInstructorsForCollege();

                        // ✅ Select "By Program" as default on load
                        document.querySelector('.view-type-btn[data-view="program"]').click();
                    });
                    </script>
                </div>
            </div>
        </div>

        <!-- Weekly Calendar -->
        <div class="schedule-calendar-container" id="schedules-container">
            <div class="schedule-calendar" id="schedule-calendar">
                <!-- Calendar will be generated here -->
            </div>
        </div>
    </div>

    <style>
    .schedule-calendar-container {
        overflow-x: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }

    .schedule-calendar {
        display: grid;
        grid-template-columns: 80px repeat(6, 1fr);
        min-width: 800px;
        background: white;
    }

    .time-header,
    .day-header {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 8px;
        font-weight: 600;
        text-align: center;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .time-slot {
        border: 1px solid #dee2e6;
        padding: 4px;
        font-size: 12px;
        text-align: center;
        background: #f8f9fa;
        font-weight: 500;
    }

    .schedule-cell {
        border: 1px solid #dee2e6;
        min-height: 40px;
        position: relative;
        background: white;
    }

    .schedule-item {
        background: #9e2a2f;
        color: white;
        padding: 4px 6px;
        border-radius: 3px;
        font-size: 11px;
        margin: 1px;
        cursor: pointer;
        position: absolute;
        left: 2px;
        right: 2px;
        overflow: hidden;
        z-index: 5;
        border: 1px solid #731f22;
    }

    .schedule-item:hover {
        background: #731f22;
        z-index: 10;
    }

    .schedule-item .course-code {
        font-weight: bold;
        display: block;
        line-height: 1.2;
    }

    .schedule-item .program-info {
        font-size: 9px;
        opacity: 0.9;
        line-height: 1.1;
    }

    .schedule-item .time-info {
        font-size: 9px;
        opacity: 0.8;
        line-height: 1.1;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Time slots from 7:00 AM to 9:00 PM (30-min intervals, no 9:30 PM)
        const generateTimeSlots = () => {
            const slots = [];
            for (let hour = 7; hour <= 21; hour++) {
                const hour12 = hour > 12 ? hour - 12 : hour;
                const period = hour >= 12 ? 'PM' : 'AM';

                slots.push(`${hour12}:00 ${period}`);

                // Don't add 30-minute slot for 9 PM
                if (hour < 21) {
                    slots.push(`${hour12}:30 ${period}`);
                }
            }
            return slots;
        };

        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const timeSlots = generateTimeSlots();

        // Create calendar grid
        const createCalendarGrid = () => {
            const calendar = document.getElementById('schedule-calendar');
            calendar.innerHTML = '';

            // Header row
            calendar.innerHTML += '<div class="time-header">Time</div>';
            days.forEach(day => {
                calendar.innerHTML += `<div class="day-header">${day}</div>`;
            });

            // Time rows
            timeSlots.forEach(timeSlot => {
                calendar.innerHTML += `<div class="time-slot">${timeSlot}</div>`;
                days.forEach(day => {
                    calendar.innerHTML +=
                        `<div class="schedule-cell" data-day="${day}" data-time="${timeSlot}"></div>`;
                });
            });
        };

        // Convert time string to minutes for comparison
        const timeToMinutes = (timeStr) => {
            const [time, period] = timeStr.split(' ');
            const [hours, minutes] = time.split(':').map(Number);
            let totalHours = hours;
            if (period === 'PM' && hours !== 12) totalHours += 12;
            if (period === 'AM' && hours === 12) totalHours = 0;
            return totalHours * 60 + minutes;
        };

        // Format time for display
        const formatTime = (timeStr) => {
            const [hours, minutes] = timeStr.split(':').map(Number);
            const period = hours >= 12 ? 'PM' : 'AM';
            const hour12 = hours % 12 || 12;
            return `${hour12}:${minutes.toString().padStart(2, '0')} ${period}`;
        };

        // Only one view at a time, only relevant filters visible
        const getCurrentScheduleFilters = () => {
            const viewType = document.getElementById('viewTypeSelect').value;
            if (viewType === 'program') {
                return {
                    view_type: viewType,
                    program: document.getElementById('programFilterSelect').value,
                    section: document.getElementById('sectionFilterSelect').value
                };
            } else if (viewType === 'instructor') {
                return {
                    view_type: viewType,
                    instructor: document.getElementById('instructorFilterSelect').value
                };
            }
            return {
                view_type: viewType
            };
        };

        const toggleFilterVisibility = () => {
            const viewType = document.getElementById('viewTypeSelect').value;
            document.getElementById('programSectionFilters').style.display = (viewType === 'program') ?
                'flex' : 'none';
            document.getElementById('instructorFilters').style.display = (viewType === 'instructor') ?
                'flex' : 'none';
        };

        // Calculate schedule item position and height
        const calculateSchedulePosition = (startTime, endTime) => {
            const startMinutes = timeToMinutes(startTime);
            const endMinutes = timeToMinutes(endTime);

            // Find the starting slot index
            let startSlotIndex = -1;
            let endSlotIndex = -1;

            for (let i = 0; i < timeSlots.length; i++) {
                const slotMinutes = timeToMinutes(timeSlots[i]);
                if (startSlotIndex === -1 && slotMinutes >= startMinutes) {
                    startSlotIndex = i;
                }
                if (slotMinutes < endMinutes) {
                    endSlotIndex = i;
                }
            }

            const slotHeight = 40; // min-height of schedule-cell
            const slotsSpanned = Math.max(1, endSlotIndex - startSlotIndex + 1);

            return {
                startSlotIndex,
                top: 0,
                height: (slotsSpanned * slotHeight) - 2 // -2 for margins
            };
        };

        // Load schedules
        const loadSchedules = () => {
            const filters = getCurrentScheduleFilters();

            // Only fetch if section is selected for program view, or instructor for instructor view
            if (!filters.view_type ||
                (filters.view_type === 'program' && (!filters.program || !filters.section)) ||
                (filters.view_type === 'instructor' && !filters.instructor)) {

                document.querySelectorAll('.schedule-cell').forEach(cell => {
                    cell.innerHTML = '';
                });
                return;
            }

            let url = `includes/tabs/get_table.php?table=user_schedules&all=1`;
            if (filters.program) url += `&program=${encodeURIComponent(filters.program)}`;
            if (filters.section) url += `&section=${encodeURIComponent(filters.section)}`;
            if (filters.instructor) url += `&instructor=${encodeURIComponent(filters.instructor)}`;

            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    // Clear existing schedule items
                    document.querySelectorAll('.schedule-cell').forEach(cell => {
                        cell.innerHTML = '';
                    });

                    if (data.data && data.data.length > 0) {
                        data.data.forEach(schedule => {
                            const startTime = formatTime(schedule.start_time);
                            const endTime = formatTime(schedule.end_time);
                            const position = calculateSchedulePosition(startTime, endTime);

                            // Find the starting cell for this schedule
                            const startCell = document.querySelector(
                                `[data-day="${schedule.day_of_week}"][data-time="${timeSlots[position.startSlotIndex]}"]`
                            );

                            if (startCell && position.startSlotIndex !== -1) {
                                const scheduleItem = document.createElement('div');
                                scheduleItem.className = 'schedule-item';
                                scheduleItem.style.height = `${position.height}px`;
                                scheduleItem.style.top = `${position.top}px`;

                                const viewType = filters.view_type;
                                let displayContent = '';

                                // --- Add edit/delete buttons ---
                                displayContent += `
                                    <div class="d-flex justify-content-end gap-1 mb-1">
                                        <button class="btn btn-sm btn-primary edit-btn" 
                                            data-table="user_schedules" data-id="${schedule.id}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-btn" 
                                            data-table="user_schedules" data-id="${schedule.id}" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                `;

                                if (viewType === 'program') {
                                    displayContent += `
                                        <span class="course-code">${schedule.class_name}</span> <br>
                                        <span class="program-info">${schedule.program_name || 'N/A'}</span> <br>
                                        <span class="section-info">Section: ${schedule.section || 'N/A'}</span> <br>
                                        <span class="instructor">
                                            ${schedule.first_name ? `${schedule.first_name} ${schedule.last_name}` : 'N/A'}
                                        </span> <br>
                                        <span class="time-info">${startTime} - ${endTime}</span> <br>
                                    `;
                                } else if (viewType === 'instructor') {
                                    displayContent += `
                                        <span class="course-code">${schedule.class_name}</span> <br>
                                        <span class="program-info">${schedule.program_name || 'N/A'}</span> <br>
                                        <span class="section-info">Section: ${schedule.section || 'N/A'}</span> <br>
                                        <span class="instructor">
                                            ${schedule.first_name ? `${schedule.first_name} ${schedule.last_name}` : 'N/A'}
                                        </span> <br>
                                        <span class="time-info">${startTime} - ${endTime}</span>
                                    `;
                                }

                                scheduleItem.innerHTML = displayContent;
                                const deleteBtn = scheduleItem.querySelector('.delete-btn');
                                if (deleteBtn) {
                                    const selectedScheduleLabel = `${schedule.class_name || 'Class'} (${schedule.day_of_week || 'Day'} ${startTime} - ${endTime})`;
                                    deleteBtn.dataset.deleteLabel = selectedScheduleLabel;
                                }
                                scheduleItem.setAttribute('data-id', schedule.id);
                                scheduleItem.setAttribute('title',
                                    `${schedule.class_name} (${startTime} - ${endTime})`);

                                // --- Remove old click-to-edit logic ---

                                startCell.appendChild(scheduleItem);
                            }
                        });

                        // --- Connect edit/delete buttons to main modal logic ---
                        // Use event delegation for dynamically added buttons
                        document.querySelectorAll('.schedule-item .edit-btn').forEach(btn => {
                            btn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                // Trigger main edit modal logic
                                $(this).trigger('click.editBtn');
                            });
                        });
                        document.querySelectorAll('.schedule-item .delete-btn').forEach(btn => {
                            btn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                // Trigger main delete modal logic
                                $(this).trigger('click.deleteBtn');
                            });
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading schedules:', error);
                });
        };

        // Initialize calendar
        const initializeSchedulesTab = () => {
            const schedulesTab = document.getElementById('schedules');
            if (schedulesTab && (schedulesTab.classList.contains('active') || schedulesTab.classList
                    .contains('show'))) {
                createCalendarGrid();
                // Do not load schedules on init. Wait for user filter selection.
            }
        };

        // Filter events (only relevant ones)
        document.getElementById('viewTypeSelect').addEventListener('change', function() {
            toggleFilterVisibility();
            // Reset other filters when view changes
            document.getElementById('programFilterSelect').value = "";
            document.getElementById('sectionFilterSelect').innerHTML =
                '<option value="">Select Section</option>';
            document.getElementById('sectionFilterSelect').disabled = true;
            document.getElementById('instructorFilterSelect').value = "";

            const college = document.getElementById('collegeFilterSelect').value;
            if (this.value === 'program' && college) {
                fetch(`includes/tabs/load_programs.php?college=${encodeURIComponent(college)}`)
                    .then(res => {
                        if (!res.ok) throw new Error('Network response was not ok');
                        return res.text();
                    })
                    .then(html => {
                        document.getElementById('programFilterSelect').innerHTML =
                            '<option value="">Select Program</option>' + html;
                        document.getElementById('programFilterSelect').disabled = false;
                    })
                    .catch(err => {
                        console.error('Error loading programs for college:', err);
                        alert('Failed to load programs for selected college.');
                    });
            }

            if (this.value === 'instructor') {
                document.getElementById('instructorFilterSelect').innerHTML =
                    '<option value="">Select Instructors</option>';
                window.loadInstructorsForCollege(college);
            }

            updateExportTriggerLabel();

            loadSchedules(); // This will clear the board
        });
        document.getElementById('programFilterSelect').addEventListener('change', function() {
            // Section dropdown is populated by its own event above
            loadSchedules();
        });
        document.getElementById('sectionFilterSelect').addEventListener('change', loadSchedules);
        document.getElementById('instructorFilterSelect').addEventListener('change', loadSchedules);

        // Tab visibility event listeners
        document.querySelectorAll('#v-pills-tab .nav-link').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                if (e.target.id === 'schedules-tab') {
                    initializeSchedulesTab();
                }
            });
        });

        // Initialize
        setTimeout(initializeSchedulesTab, 100);
        setTimeout(initializeSchedulesTab, 500);
        setTimeout(initializeSchedulesTab, 1000);

        // Make functions available globally for compatibility
        window.getCurrentScheduleFilters = getCurrentScheduleFilters;
        window.reloadCurrentScheduleView = loadSchedules;

        // Initialize view toggle
        toggleFilterVisibility();
    });
    </script>
</div>