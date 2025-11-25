# 📊 System Architecture - Section-Based Access Control

## Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                     PROFESSOR LOGIN                             │
│                     (usertype = 2)                              │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ▼
         ┌───────────────────┐
         │   get_table.php   │
         │  (Dashboard API)  │
         └────────┬──────────┘
                  │
     ┌────────────┼────────────┐
     │            │            │
     ▼            ▼            ▼
  Users       Teams       Defense
  Table       Table      Schedules
     │            │            │
     └────┬───────┴───┬────────┘
          │           │
          ▼           ▼
    ┌─────────────────────────────────┐
    │  section_access.php             │
    │  getProfessorSection()           │
    │  Get: professor_id → section     │
    └────────┬────────────────────────┘
             │
             ▼
    ┌─────────────────────────────────┐
    │  section_professors table       │
    │  Professor 1 → Section A        │
    │  Professor 2 → Section B        │
    └────────┬────────────────────────┘
             │
             ▼
    Add WHERE clause:
    AND users.section = 'Section A'
             │
             ▼
    ┌─────────────────────────────────┐
    │  Return Filtered Results        │
    │  Only Section A students/data   │
    └─────────────────────────────────┘
```

## Component Interactions

```
┌──────────────────────────────────────────────────────────────┐
│                    DASHBOARD (Frontend)                      │
│                                                              │
│  Users Tab    Teams Tab    Schedules Tab    Evaluations Tab  │
└───────────┬─────────────────────┬──────────────────┬────────┘
            │                     │                  │
            └─────────────────────┼──────────────────┘
                                  │
                    ┌─────────────▼──────────────┐
                    │   AJAX Request to          │
                    │   get_table.php            │
                    │   ?table=users&page=1      │
                    └──────────────┬─────────────┘
                                   │
                    ┌──────────────▼──────────────┐
                    │  Check User Type & Section  │
                    │  if (usertype === 2) {      │
                    │    getSection()             │
                    │    addFilter()              │
                    │  }                          │
                    └──────────────┬──────────────┘
                                   │
                    ┌──────────────▼──────────────┐
                    │  Execute Filtered Query    │
                    │  SELECT * FROM users       │
                    │  WHERE section = 'Sec A'   │
                    └──────────────┬──────────────┘
                                   │
                    ┌──────────────▼──────────────┐
                    │  Return JSON Response      │
                    │  {data: [...]}             │
                    └──────────────┬──────────────┘
                                   │
                    ┌──────────────▼──────────────┐
                    │  Render in Table on UI     │
                    │  Professor sees only their │
                    │  section's students        │
                    └────────────────────────────┘
```

## Database Schema

```
┌──────────────────────────┐
│  users                   │
├──────────────────────────┤
│ id                       │
│ first_name               │
│ last_name                │
│ usertype (0/1/2)         │
│ section ← Filter by this │
└──────────────────────────┘
           ▲
           │
           │ Join on professor_id
           │
┌──────────────────────────┐
│  section_professors      │
├──────────────────────────┤
│ id (PK)                  │
│ professor_id (FK)  ──────┼─→ users.id
│ section (FK)       ──────┼─→ users.section
│ status                   │
│ assigned_at              │
└──────────────────────────┘

When professor logs in:
1. Query: SELECT section FROM section_professors 
          WHERE professor_id = ?
2. Get: "Section A"
3. Apply: AND users.section = "Section A" to all queries
```

## Access Matrix

```
                 │ Admin │ Prof+Section │ Prof-NoSection │ Student
─────────────────┼───────┼──────────────┼────────────────┼─────────
Users Table      │ All   │ Own section  │ All            │ Limited
Teams Table      │ All   │ Own section  │ All            │ Own team
Schedules Table  │ All   │ Own section  │ All            │ Own team
Evaluations      │ All   │ Own section  │ All            │ Own team
Edit/Delete      │ Yes   │ Own section  │ Own section    │ No
```

## Setup Flow

```
START
  │
  ▼
Visit init_section_professors.php
  │
  ▼
section_professors table created
  │
  ▼
Admin adds students (with sections)
  │
  ├─ Student A (section = "Section A")
  ├─ Student B (section = "Section B")
  │
  ▼
Admin adds faculty (usertype = 2)
  │
  ├─ Professor 1
  ├─ Professor 2
  │
  ▼
Admin assigns professors to sections
  │
  ├─ Professor 1 → Section A
  │  (Insert: professor_id=101, section="Section A")
  │
  ├─ Professor 2 → Section B
  │  (Insert: professor_id=102, section="Section B")
  │
  ▼
Professor 1 logs in
  │
  ▼
system.getProfessorSection(101) → "Section A"
  │
  ▼
All queries filtered: AND users.section = "Section A"
  │
  ▼
Professor 1 sees ONLY Section A students/data
  │
  ▼
DONE ✓
```

## Key Filter Logic

```php
// Pseudo-code showing how filtering works

if (userType === 2) {  // Faculty
    section = database.getProfessorSection(userId);
    
    if (section !== null) {  // Has assignment
        query += " AND users.section = '" + section + "'";
    } else {  // No assignment
        // No filter, see all data
    }
}

// Result: Only faculty with section assignment are filtered
// Backwards compatible: Faculty without section see everything
```

## Performance Considerations

✅ Indexed queries: section column has INDEX  
✅ JOIN efficient: section_professors has small footprint  
✅ Cache friendly: Section assignments rarely change  
✅ Scalable: O(1) lookup of professor's section  

---

**Visual guide complete!** 📊
