const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  Header, Footer, AlignmentType, HeadingLevel, BorderStyle, WidthType,
  ShadingType, VerticalAlign, PageNumber, PageBreak, TabStopType,
  TabStopPosition, LevelFormat
} = require('docx');
const fs = require('fs');

// ── Colours ──────────────────────────────────────────────────────────────────
const GREEN  = "1A5C1A";   // dark forest green (headings, accent)
const GREEN2 = "2E7D32";   // medium green (sub-headings)
const TEAL   = "006064";   // teal for special program sections
const DARK   = "1A1A2E";   // near-black body text
const GRAY   = "4A5568";   // secondary text
const LGRAY  = "F0F4F0";   // light green-grey row tint
const WHITE  = "FFFFFF";
const GHDR   = "1A5C1A";   // table header green fill

// ── Shared border ────────────────────────────────────────────────────────────
const thinBorder = { style: BorderStyle.SINGLE, size: 1, color: "CCCCCC" };
const allBorders = { top: thinBorder, bottom: thinBorder, left: thinBorder, right: thinBorder };
const noBorder   = { style: BorderStyle.NONE, size: 0, color: "FFFFFF" };
const noBorders  = { top: noBorder, bottom: noBorder, left: noBorder, right: noBorder };

// ── Helpers ───────────────────────────────────────────────────────────────────
function cell(text, opts = {}) {
  const {
    bold = false, color = DARK, fill = null, shade = ShadingType.CLEAR,
    width = null, colSpan, align = AlignmentType.LEFT, fontSize = 20
  } = opts;
  return new TableCell({
    columnSpan: colSpan,
    borders: allBorders,
    width: width ? { size: width, type: WidthType.DXA } : undefined,
    shading: fill ? { fill, type: shade } : undefined,
    verticalAlign: VerticalAlign.CENTER,
    margins: { top: 80, bottom: 80, left: 120, right: 120 },
    children: [new Paragraph({
      alignment: align,
      children: [new TextRun({ text, bold, color, size: fontSize, font: "Arial" })]
    })]
  });
}

function hdrCell(text, width = null) {
  return cell(text, { bold: true, color: WHITE, fill: GHDR, shade: ShadingType.CLEAR, width, fontSize: 20 });
}

function twoColRow(label, value, shade = false) {
  return new TableRow({ children: [
    cell(label, { bold: true, color: GREEN, fill: shade ? LGRAY : null, width: 3500, fontSize: 20 }),
    cell(value,  { fill: shade ? LGRAY : null, width: 6100, fontSize: 20 }),
  ]});
}

function simpleTable(headers, rows, widths) {
  return new Table({
    width: { size: 9360, type: WidthType.DXA },
    columnWidths: widths,
    rows: [
      new TableRow({ tableHeader: true, children: headers.map((h, i) => hdrCell(h, widths[i])) }),
      ...rows.map((row, ri) => new TableRow({
        children: row.map((val, ci) => cell(val, { fill: ri % 2 === 1 ? LGRAY : null, width: widths[ci] }))
      }))
    ]
  });
}

function h1(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_1,
    pageBreakBefore: true,
    children: [new TextRun({ text, bold: true, color: GREEN, size: 36, font: "Arial" })]
  });
}

function h2(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_2,
    children: [new TextRun({ text, bold: true, color: GREEN2, size: 28, font: "Arial" })]
  });
}

function h3(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_3,
    children: [new TextRun({ text, bold: true, color: TEAL, size: 24, font: "Arial" })]
  });
}

function body(text, opts = {}) {
  const { bold = false, color = DARK, italic = false } = opts;
  return new Paragraph({
    spacing: { after: 120 },
    children: [new TextRun({ text, bold, color, italic, size: 22, font: "Arial" })]
  });
}

function bullet(text) {
  return new Paragraph({
    numbering: { reference: "bullets", level: 0 },
    spacing: { after: 60 },
    children: [new TextRun({ text, size: 22, font: "Arial", color: DARK })]
  });
}

function gap(space = 100) {
  return new Paragraph({ spacing: { after: space }, children: [] });
}

function greenLine() {
  return new Paragraph({
    border: { bottom: { style: BorderStyle.SINGLE, size: 8, color: GREEN, space: 1 } },
    spacing: { after: 160 },
    children: []
  });
}

// ═══════════════════════════════════════════════════════════════════════════════
//  DOCUMENT SECTIONS CONTENT
// ═══════════════════════════════════════════════════════════════════════════════

function coverPage() {
  return [
    gap(2000),
    new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: "FDE Admission Portal 2026–27", bold: true, size: 64, color: GREEN, font: "Arial" })] }),
    gap(120),
    new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: "Complete System Documentation", size: 36, color: GREEN2, font: "Arial", italic: true })] }),
    gap(600),
    greenLine(),
    gap(200),
    new Table({
      width: { size: 9360, type: WidthType.DXA },
      columnWidths: [2800, 6560],
      rows: [
        twoColRow("Client",          "Federal Directorate of Education (FDE), Islamabad"),
        twoColRow("System Name",     "FDE Admission Portal"),
        twoColRow("Academic Year",   "2026–27"),
        twoColRow("Document Version","3.0"),
        twoColRow("Prepared",        "April 2026"),
        twoColRow("Designed & Developed By", "InnovaMaven"),
      ]
    }),
    gap(300),
    new Paragraph({ children: [new PageBreak()] }),
  ];
}

function tocPage() {
  const entry = (num, title, sub = false) => new Paragraph({
    spacing: { after: sub ? 60 : 100 },
    indent: sub ? { left: 360 } : {},
    children: [
      new TextRun({ text: `${num}  ${title}`, size: sub ? 20 : 22, font: "Arial", color: sub ? GRAY : DARK, bold: !sub }),
    ]
  });

  return [
    new Paragraph({ heading: HeadingLevel.HEADING_1, children: [new TextRun({ text: "Table of Contents", bold: true, color: GREEN, size: 36, font: "Arial" })] }),
    greenLine(),
    gap(60),
    // Sections
    entry("1.", "Project Overview"),
    entry("2.", "Technology Stack"),
    entry("3.", "Geographic Structure"),
    entry("4.", "User Roles & Access Levels"),
    entry("5.", "Role — Admin"),
    entry("6.", "Role — HOI (Head of Institution)"),
    entry("7.", "Role — AEO (Area Education Officer)"),
    entry("8.", "Role — Director"),
    entry("9.", "Role — FDE Cell"),
    entry("", "── MODULES ──", true),
    entry("Module 01", "Authentication & Account Management"),
    entry("Module 02", "Admin Panel"),
    entry("Module 03", "School Profile Setup"),
    entry("Module 04", "Facilities Setup"),
    entry("Module 05", "Class & Section Setup"),
    entry("Module 06", "Baseline Enrollment"),
    entry("Module 07", "Daily Admissions Entry"),
    entry("Module 08", "Admission Report (HOI)"),
    entry("Module 09", "Admission Quota"),
    entry("Module 10", "Student Transfers"),
    entry("Module 11", "Student Referrals"),
    entry("Module 12", "Reports & Analytics", false),
    entry("",  "12.1  Master Report (School-by-School, Class-by-Class)", true),
    entry("",  "12.2  Sector Report", true),
    entry("",  "12.3  Gender Report", true),
    entry("",  "12.4  OOSC Report (Out-of-School Children)", true),
    entry("",  "12.5  Vacancy Report", true),
    entry("",  "12.6  Report Dashboard (Charts)", true),
    entry("",  "12.7  Schools Report", true),
    entry("",  "12.8  College Reports (Model & Ex-FG)", true),
    entry("",  "12.9  Staff Strength Report (PDF & Excel)", true),
    entry("",  "12.10 UC Control Rooms Report (PDF)", true),
    entry("Module 13", "Merit Lists"),
    entry("Module 14", "Staff Management"),
    entry("",  "14.1  HOI — Enter & Submit Staff Strength", true),
    entry("",  "14.2  FDE — Review, Verify, Lock, Export", true),
    entry("",  "14.3  AEO / Director — View Staff Stats", true),
    entry("Module 15", "New Construction Rooms"),
    entry("Module 16", "Admission Corrections"),
    entry("Module 17", "Admission Edit Grants"),
    entry("Module 18", "Seat Configuration (FDE Override)"),
    entry("Module 19", "Enrollment Override"),
    entry("Module 20", "Admission Period Control"),
    entry("Module 21", "Monitoring & School Tracking"),
    entry("Module 22", "Colleges (Model & Ex-FG)"),
    entry("Module 23", "UC Control Rooms"),
    entry("Module 24", "Audit Log"),
    entry("Module 25", "Public Portal", false),
    entry("",  "25.1  School Search Page", true),
    entry("",  "25.2  School Detail Page", true),
    entry("",  "25.3  Seat Availability Page", true),
    entry("",  "25.4  Merit Lists Page", true),
    entry("",  "25.5  Portal Settings (FDE Controlled)", true),
    entry("Module 26", "Announcements"),
    entry("Module 27", "Notifications"),
    entry("Module 28", "AI Reports (AI Studio)"),
    entry("Module 29", "App Settings"),
    entry("Module 30", "Theme & Appearance"),
    entry("Module 31", "Portal Settings"),
    entry("Module 32", "System Reset"),
    entry("", "── SPECIAL PROGRAMS & DASHBOARDS ──", true),
    entry("", "Special Programs (OOSC, P2G, Matric Tech, ECE, Cambridge, Dual-Shift)", false),
    entry("", "Dashboard — HOI", false),
    entry("", "Dashboard — AEO", false),
    entry("", "Dashboard — Director", false),
    entry("", "Dashboard — FDE Cell", false),
    entry("", "Data Flow — End-to-End Summary", false),
    new Paragraph({ children: [new PageBreak()] }),
  ];
}

// ── Section 1: Project Overview ───────────────────────────────────────────────
function section1() {
  return [
    h1("1. Project Overview"),
    greenLine(),
    h2("What Is the FDE Admission Portal?"),
    body("The FDE Admission Portal 2026–27 is a secure, centralized, web-based school admission management system built exclusively for the Federal Directorate of Education (FDE), Islamabad. It replaces paper-based and spreadsheet-based admission tracking with a live digital platform that connects every school head, area officer, and FDE department in one unified system."),
    gap(),
    h2("The Problem It Solves"),
    body("Before this portal, FDE schools submitted admission data manually through paper forms and Excel spreadsheets. This caused:"),
    bullet("No real-time visibility into how many students have been admitted across 100+ schools"),
    bullet("No way to enforce seat capacity limits — schools could over-admit without detection"),
    bullet("No centralized tracking of special programs (OOSC, P2G, Matric Tech)"),
    bullet("Manual data aggregation taking days to produce reports"),
    bullet("Parents had no way to check school availability or seat counts publicly"),
    bullet("Errors and corrections required physical paperwork"),
    bullet("No visibility into staff strength across schools"),
    gap(),
    h2("What the System Delivers"),
    simpleTable(
      ["Stakeholder", "What They Get"],
      [
        ["HOI / School Head", "Step-by-step digital process: profile setup → facilities → class setup → daily admissions → staff strength → reports"],
        ["AEO", "Live dashboard for their sector: seat data, enrollment, admissions, Matric Tech, staff stats, new rooms"],
        ["Director", "System-wide read-only dashboard with all statistics and school-level drill-down"],
        ["FDE Cell", "Full operational control: reports, corrections, transfers, referrals, seat overrides, staff verification, period control, announcements, settings"],
        ["Public / Parents", "No-login school search portal to find schools, check seats, view merit lists"],
      ],
      [3000, 6360]
    ),
  ];
}

// ── Section 2: Technology Stack ───────────────────────────────────────────────
function section2() {
  return [
    h1("2. Technology Stack"),
    greenLine(),
    simpleTable(
      ["Component", "Technology"],
      [
        ["Backend Framework",  "Laravel 10 (PHP 8.2)"],
        ["Database",           "MySQL"],
        ["Frontend",           "Blade Templates, Alpine.js (reactive UI), Tailwind CSS"],
        ["Authentication",     "Laravel Auth + Spatie Permission (role-based access control)"],
        ["PDF Export",         "Laravel DomPDF (Barryvdh)"],
        ["Excel Export",       "Maatwebsite Laravel Excel"],
        ["Email",              "Laravel Mail (corrections, notifications)"],
        ["Deployment",         "Apache / Nginx web server"],
      ],
      [3500, 5860]
    ),
    gap(),
    h2("Security Model"),
    bullet("Every route is role-protected via Spatie Permission middleware"),
    bullet("HOIs can only see and modify data for their own assigned institution"),
    bullet("AEOs only see institutions within their assigned sector(s)"),
    bullet("All destructive changes are recorded in the audit log"),
    bullet("Password reset via secure email link"),
    bullet("Maintenance mode locks out all non-admin users"),
  ];
}

// ── Section 3: Geographic Structure ──────────────────────────────────────────
function section3() {
  return [
    h1("3. Geographic Structure"),
    greenLine(),
    body("The system mirrors FDE's administrative geography:"),
    gap(80),
    new Paragraph({ children: [new TextRun({ text: "FDE (Federal Directorate of Education)", bold: true, size: 22, font: "Courier New", color: GREEN })] }),
    new Paragraph({ indent: { left: 360 }, children: [new TextRun({ text: "└── Sectors  (Urban-I, Urban-II, B-K, Tarnol, Sihala, Nilore, Model Colleges)", size: 22, font: "Courier New", color: DARK })] }),
    new Paragraph({ indent: { left: 720 }, children: [new TextRun({ text: "└── Union Councils (UCs)", size: 22, font: "Courier New", color: DARK })] }),
    new Paragraph({ indent: { left: 1080 }, children: [new TextRun({ text: "└── Schools / Institutions", size: 22, font: "Courier New", color: DARK })] }),
    new Paragraph({ indent: { left: 1440 }, children: [new TextRun({ text: "└── Classes (Nursery → Class 12, ECE)", size: 22, font: "Courier New", color: DARK })] }),
    new Paragraph({ indent: { left: 1800 }, children: [new TextRun({ text: "└── Sections (A, B, C …)", size: 22, font: "Courier New", color: DARK })] }),
    gap(),
    body("Sectors group schools geographically and are the primary unit for AEO assignment and FDE reporting. Union Councils are sub-units within sectors used for HOI profile setup and UC Control Room tracking."),
  ];
}

// ── Section 4: Roles ──────────────────────────────────────────────────────────
function section4() {
  return [
    h1("4. User Roles & Access Levels"),
    greenLine(),
    simpleTable(
      ["Role", "Who Uses It", "Scope", "Key Capabilities"],
      [
        ["Admin",     "IT / System Administrator", "System-wide", "Create users, manage schools, configure academic years, bulk import"],
        ["HOI",       "Head of Institution",        "Own school only", "Profile, class setup, admissions, staff strength, corrections, transfers"],
        ["AEO",       "Area Education Officer",     "Assigned sector(s)", "Monitor schools, view class data, admissions, staff stats, new rooms"],
        ["Director",  "FDE Director",               "System-wide", "Read-only monitoring of all schools and statistics"],
        ["FDE Cell",  "FDE Admission Staff",        "System-wide", "Full control: reports, corrections, transfers, referrals, seat config, staff, settings"],
      ],
      [1500, 2100, 1800, 3960]
    ),
    gap(),
    body("Important: Roles are strictly enforced. A HOI cannot access another school's data. An AEO cannot access schools outside their sector. Directors cannot edit data.", { bold: true, color: "C0392B" }),
  ];
}

// ── Roles 5–9 ────────────────────────────────────────────────────────────────
function section5to9() {
  return [
    h1("5. Role — Admin"),
    greenLine(),
    body("System administrator(s) responsible for configuring the platform before the admission season begins."),
    gap(),
    h2("5.1  User Management"),
    bullet("Create, edit, activate, and deactivate user accounts"),
    bullet("Assign roles: HOI, AEO, FDE Cell, Director"),
    bullet("Link HOI accounts to their institution; assign AEOs to one or more sectors"),
    bullet("Search and filter by name, email, role, or status"),
    h2("5.2  Institution (School) Management"),
    bullet("Register schools with: name, EMIS code, type, sector, UC, gender, shift"),
    bullet("Edit and activate/deactivate schools; filter by sector, type, gender, status"),
    h2("5.3  Sector & Union Council Management"),
    bullet("Create, edit, and manage Sectors (name, code, active flag)"),
    bullet("Create and manage Union Councils linked to sectors"),
    h2("5.4  Academic Year Management"),
    bullet("Create academic years and set the active year"),
    bullet("Configure admission start date, end date, and daily submission cutoff time"),
    bullet("All admissions, reports, and statistics are scoped to the active academic year"),
    h2("5.5  Bulk Import"),
    bullet("Import schools and users in bulk via CSV/Excel upload"),
    gap(),
    h1("6. Role — HOI (Head of Institution)"),
    greenLine(),
    body("The principal or head of each FDE school. Each HOI account is linked to exactly one institution."),
    gap(),
    h2("HOI Workflow (Step by Step)"),
    bullet("Step 1: Profile Setup → Select UC, sector, school; confirm gender and shift"),
    bullet("Step 2: Facilities Setup → Declare programs (ECE, Matric Tech, evening, transport, etc.)"),
    bullet("Step 3: Class & Section Setup → Configure active classes, seats, sections, existing enrollment"),
    bullet("Step 4: Baseline Enrollment → (Optional) Submit promoted/failed breakdown for FDE review"),
    bullet("Step 5: Admission Quota → (Optional) Set per-class intake quotas"),
    bullet("Step 6: Daily Admissions → Enter today's new admissions class by class, every working day"),
    bullet("Step 7: Admission Report → View own cumulative admission summary"),
    bullet("Step 8: Staff Strength → Enter teaching and program staff data for FDE verification"),
    bullet("Step 9: Other Modules → Transfers, corrections, rooms, referrals, merit lists"),
    gap(),
    h1("7. Role — AEO (Area Education Officer)"),
    greenLine(),
    body("Government officers responsible for education quality in one or more geographic sectors. AEOs monitor — they do not enter data."),
    gap(),
    bullet("Live dashboard: sector-level summary with seats, enrollment, admissions, available capacity"),
    bullet("Matric Tech totals: existing baseline + this year admitted + combined total"),
    bullet("Staff Strength overview: which schools have submitted, verified, or locked their registers"),
    bullet("New Construction Rooms: total, allocated, remaining"),
    bullet("School-wise drill-down into any individual school's full admission and class history"),
    bullet("Multi-sector support: AEOs assigned to multiple sectors see data aggregated across all of them"),
    gap(),
    h1("8. Role — Director"),
    greenLine(),
    body("Senior FDE Director with system-wide oversight responsibility. Read-only monitoring — Directors see all data FDE Cell sees but cannot make changes."),
    gap(),
    bullet("Full system-wide statistics (all sectors combined)"),
    bullet("Per-sector breakdown table"),
    bullet("Staff Strength status overview across all schools"),
    bullet("School-level monitoring drill-down"),
    bullet("No create, edit, or delete access anywhere in the system"),
    gap(),
    h1("9. Role — FDE Cell"),
    greenLine(),
    body("FDE admission department staff responsible for running the portal operationally. Full system control."),
    gap(),
    simpleTable(
      ["Capability", "Description"],
      [
        ["Dashboard",              "System-wide admission stats with sector breakdown and non-submitting schools list"],
        ["Admission Monitoring",   "View all school submissions, filter by date/school"],
        ["Admission Corrections",  "Approve or reject HOI correction requests; email HOI decision"],
        ["Admission Edit Grants",  "Grant/revoke time-limited permission for HOI to edit past locked entries"],
        ["Student Transfers",      "Create, approve, reject cross-school student transfers"],
        ["Student Referrals",      "Create and manage student referral letters to schools"],
        ["Seat Configuration",     "Override school seat counts; lock/unlock seats"],
        ["Enrollment Override",    "Override a school's existing enrollment directly"],
        ["Admission Period",       "Set admission dates/cutoff; open/close admissions system-wide"],
        ["Staff Management",       "View all school staff registers; verify, return, lock; export PDF & Excel"],
        ["Colleges",               "View Model Colleges and Ex-FG Colleges with admission stats"],
        ["UC Control Rooms",       "Directory of all UC control room focal persons and contacts"],
        ["Reports & Analytics",    "Master, sector, gender, OOSC, vacancy, schools, college, staff reports"],
        ["AI Studio",              "Natural-language data queries"],
        ["Audit Log",              "Full tamper-evident change log"],
        ["Announcements",          "Create and manage system-wide notices targeted by role"],
        ["App Settings",           "Branding, logo, colors, support info, maintenance mode"],
        ["Theme",                  "Customize portal colors, fonts, sidebar, dark/light mode"],
        ["Portal Settings",        "Public portal announcement and contact configuration"],
        ["System Reset",           "Full data wipe and re-seed for new academic year (super admin)"],
      ],
      [3000, 6360]
    ),
  ];
}

// ── Modules 01–11 ─────────────────────────────────────────────────────────────
function modulesA() {
  return [
    // MOD 01
    h1("Module 01 — Authentication & Account Management"),
    greenLine(),
    body("Secure login, password management, and role-based access control for all users."),
    gap(),
    h2("Login"),
    bullet("Email + password authentication with role-based redirect after login"),
    bullet("HOI → HOI Dashboard (or Profile Setup if first login)"),
    bullet("AEO → AEO Dashboard | Director → Director Dashboard | FDE Cell → FDE Dashboard"),
    h2("Forgot & Reset Password"),
    bullet("User enters registered email; system sends a secure reset link via email"),
    bullet("Link expires after a set time for security"),
    h2("Account Activation"),
    bullet("Admin can activate or deactivate any user account"),
    bullet("Inactive users receive a clear message on login attempt"),
    h2("Maintenance Mode"),
    bullet("When ON (set in App Settings), all non-admin users see a maintenance page"),
    bullet("Admin can still log in and work during maintenance"),

    // MOD 02
    h1("Module 02 — Admin Panel"),
    greenLine(),
    body("System configuration and master data management. Used only by system administrators before and during the admission season."),
    gap(),
    h2("2.1  User Management"),
    bullet("Create users with name, email, phone, password, and role"),
    bullet("Link HOI users to their institution; assign AEOs to one or more sectors"),
    bullet("Edit, reset passwords, activate/deactivate; filter by role, status, school name"),
    h2("2.2  Institution (School) Management"),
    bullet("Fields: Name, EMIS code, Type (Primary, Elementary, Middle, High, Higher Secondary, Model College, Ex-FG College)"),
    bullet("Sector, Union Council, Gender (Boys/Girls/Co-ed), Shift (Morning/Evening/Both), Active status"),
    h2("2.3  Sector & Union Council Management"),
    bullet("Sectors: name, code, active flag — used for AEO assignment and public portal grouping"),
    bullet("Union Councils: linked to sectors — used in HOI profile setup and UC Control Rooms"),
    h2("2.4  Academic Year Management"),
    bullet("Year name, admission start date, admission end date, daily cutoff time"),
    bullet("Only one year is active at a time — all data is scoped to the active year"),
    h2("2.5  Bulk Import"),
    bullet("Import schools and users in bulk via CSV/Excel — used for initial system setup"),

    // MOD 03
    h1("Module 03 — School Profile Setup"),
    greenLine(),
    body("First-login step for HOI only. Links the HOI account to their school and confirms basic school attributes."),
    gap(),
    bullet("HOI selects Union Council → system auto-fills Sector → loads School dropdown via AJAX"),
    bullet("HOI confirms School, Gender (Boys/Girls/Co-ed), and Shift (Morning/Evening/Both)"),
    bullet("On submit: user account is linked to that institution; redirected to HOI Dashboard"),
    bullet("A HOI can only link to one school — re-setup is not permitted once linked"),

    // MOD 04
    h1("Module 04 — Facilities Setup"),
    greenLine(),
    body("HOI declares which special programs and physical facilities the school has. These flags control which fields appear throughout the entire system."),
    gap(),
    simpleTable(
      ["Facility Toggle", "Effect on System"],
      [
        ["ECE Center",          "Adds ECE-I and ECE-II/Prep classes to class setup and daily admissions"],
        ["Matric Tech Program", "Adds Matric Tech existing count (Class 9 & 10) and daily Matric Tech field"],
        ["Evening Classes",     "Splits all class and admission data into Morning / Evening columns everywhere"],
        ["Transport Service",   "Shown as badge on Public Portal; parents can filter by this"],
        ["Meal Program",        "Shown as badge on Public Portal"],
        ["Cambridge Program",   "Shown as badge on Public Portal; filterable in school search"],
      ],
      [3000, 6360]
    ),
    gap(),
    body("Data Safety: Disabling Evening Classes never wipes morning data — the system safely collapses both shifts into combined totals.", { bold: true }),

    // MOD 05
    h1("Module 05 — Class & Section Setup"),
    greenLine(),
    body("Configure which classes the school runs, seat counts, existing enrollment, and sections. This step is required before daily admissions can begin."),
    gap(),
    h2("Non-Evening School"),
    bullet("Existing Students — students already enrolled from previous year"),
    bullet("Total Seats — total authorized capacity per class"),
    bullet("Available Seats — auto-calculated: Total minus Existing"),
    bullet("Sections — comma-separated names (e.g., A,B,C); defaults to A"),
    h2("Evening / Dual-Shift School"),
    bullet("All fields split: Morning Existing / Evening Existing / Morning Seats / Evening Seats"),
    bullet("Morning Available and Evening Available calculated separately"),
    h2("Matric Tech Section (Class 9 & 10 only)"),
    bullet("A dedicated teal-bordered section appears if the school has has_matric_tech = true"),
    bullet("HOI enters Matric Tech Existing — previous year's Matric Tech student count"),
    bullet("Validated: Matric Tech Existing must not exceed the class's total existing students"),
    h2("ECE Classes"),
    bullet("A separate ECE section appears at the top for ECE-I and ECE-II/Prep"),
    h2("On Save"),
    bullet("All InstitutionClass records created or updated; all InstitutionSection records recreated"),
    bullet("classes_configured = true set on institution — this unlocks Daily Admissions"),

    // MOD 06
    h1("Module 06 — Baseline Enrollment"),
    greenLine(),
    body("HOI submits the detailed breakdown of existing students (promoted and failed counts) for formal FDE verification."),
    gap(),
    bullet("HOI enters promoted and failed counts per class (and per shift for evening schools)"),
    bullet("System validates: promoted + failed = existing_enrollment for each class"),
    bullet("HOI submits — FDE Cell reviews and either Verifies or Returns with a note"),
    bullet("Once verified, HOI cannot edit the baseline"),
    gap(),
    body("Status Flow: Draft → Submitted → Verified / Returned → (if Returned) Revised → Re-submitted", { bold: true, color: GREEN }),

    // MOD 07
    h1("Module 07 — Daily Admissions Entry"),
    greenLine(),
    body("The core data entry module. HOI enters how many new students were admitted today, class by class, shift by shift, and by special program — every working day during the admission period."),
    gap(),
    h2("Entry Fields Per Class"),
    simpleTable(
      ["Field", "Description"],
      [
        ["Morning Boys / Girls",    "Regular male/female admissions in the morning shift"],
        ["Evening Boys / Girls",    "Regular male/female admissions in the evening shift (evening schools only)"],
        ["OOSC Boys / Girls",       "Out-of-School Children (per shift for evening schools)"],
        ["P2G Boys / Girls",        "P2G program admissions (per shift for evening schools)"],
        ["Matric Tech Count",       "New Matric Tech students — Class 9 & 10 only, if school has Matric Tech enabled"],
        ["Available Seats",         "Real-time remaining capacity — recalculates live as numbers are entered"],
        ["Cumulative Total",        "Running total of all students admitted so far this academic year"],
      ],
      [3200, 6160]
    ),
    gap(),
    h2("Capacity Protection"),
    bullet("System blocks save if admission count would exceed available seats"),
    bullet("Capacity formula: Total Seats minus Existing Enrollment minus Previously Admitted This Year"),
    h2("Date Selection"),
    bullet("Defaults to today's date; HOI can select past dates (within academic year) for missed entries"),
    bullet("Future dates are blocked; past locked entries need an FDE Edit Grant to edit"),
    h2("Status Flow"),
    body("Draft (saved, editable) → Submitted (locked for HOI, visible to supervisors) → Verified → Locked", { bold: true, color: GREEN }),

    // MOD 08
    h1("Module 08 — Admission Report (HOI)"),
    greenLine(),
    body("Complete cumulative summary of all admissions for the school in the current academic year."),
    gap(),
    bullet("Per-class table: existing enrollment, total seats, admitted this year (all categories)"),
    bullet("Daily history: date-by-date log of all submission entries with status"),
    bullet("Totals row showing system-wide sums"),
    bullet("PDF export of the full report"),
    bullet("Vacancy sub-view: shows classes with remaining available seats"),

    // MOD 09
    h1("Module 09 — Admission Quota"),
    greenLine(),
    body("HOI can optionally set a soft per-class intake quota — a target maximum for new admissions. This is a planning tool and does not replace the hard seat-capacity limit."),
    gap(),
    bullet("HOI sets an optional quota number for each active class"),
    bullet("System shows: Quota Set / Admitted So Far / Remaining against quota"),
    bullet("Grand totals: Total Quota / Total Admitted / Total Remaining"),
    bullet("Stored in institution_classes.admission_quota — blank means no limit set"),

    // MOD 10
    h1("Module 10 — Student Transfers"),
    greenLine(),
    body("Manage the formal movement of students from one FDE school to another, with automatic enrollment count adjustments."),
    gap(),
    h2("Transfer Workflow (FDE Cell Creates)"),
    bullet("Selects From School and To School; enters per-student details (class, name, father name, notes)"),
    bullet("Multiple students can be transferred in one batch"),
    bullet("System validates both schools have the selected class configured"),
    h2("Actions"),
    simpleTable(
      ["Action", "Effect"],
      [
        ["Accept",          "Decrements existing_enrollment at sending school; increments at receiving school"],
        ["Reject",          "Transfer declined; rejection reason recorded"],
        ["Cancel",          "Transfer cancelled before any action is taken"],
        ["Cross-Sector Approve", "Cross-sector transfers require extra approval step with mandatory justification note"],
      ],
      [2800, 6560]
    ),
    gap(),
    body("Status Flow: pending → accepted / rejected / cancelled (cross-sector: pending → cross_sector_approved → accepted/rejected)", { bold: true, color: GREEN }),

    // MOD 11
    h1("Module 11 — Student Referrals"),
    greenLine(),
    body("FDE Cell formally refers a student to a specific school for admission. The school head sees the referral and acts on it."),
    gap(),
    h2("Referral Workflow"),
    bullet("FDE Cell selects target school, class, student name, gender, shift, notes"),
    bullet("System generates a unique Reference Number (e.g., REF-2026-0042)"),
    bullet("HOI views the referral and can Accept (student processed) or Reject (with reason)"),
    bullet("If rejected, FDE Cell can re-refer the student to a different school"),
    gap(),
    body("Status Flow: pending → accepted / rejected → (if rejected) FDE creates re_referred | closed (FDE cancels)", { bold: true, color: GREEN }),
    gap(),
    h2("FDE Tracking Stats"),
    bullet("Total referrals / Pending / Accepted / Rejected / Re-referred / Admitted / Not Admitted / Test Failed"),
  ];
}

// ── Module 12: Reports & Analytics (expanded) ─────────────────────────────────
function module12() {
  return [
    h1("Module 12 — Reports & Analytics"),
    greenLine(),
    body("Comprehensive, filterable reports on admission data, staff strength, schools, and colleges. Available to FDE Cell (full access) and Director (read-only). Some reports are accessible to HOI scoped to their own school."),
    gap(),

    h2("12.1  Master Report — School-by-School, Class-by-Class"),
    body("Purpose: The most comprehensive report in the system. Produces a full breakdown of every school's admission data for every class, filterable by multiple criteria."),
    gap(),
    h3("Filters Available"),
    bullet("Sector (single sector or all)"),
    bullet("School Type (Primary, Elementary, Middle, High, Higher Secondary, Model College)"),
    bullet("School Gender (Boys / Girls / Co-education)"),
    bullet("Date Range (from date to date — cumulative within this window)"),
    bullet("Class Level (All / ECE only / Non-ECE)"),
    gap(),
    h3("Data Columns Per Row (per school, per class)"),
    simpleTable(
      ["Column", "Description"],
      [
        ["School Name / Sector",   "Identifying information for the school"],
        ["Class Name",             "The class this row represents"],
        ["Existing Enrollment",    "Students already enrolled from previous year"],
        ["Total Seats",            "Total authorized seat capacity"],
        ["Available Seats",        "Total Seats minus Existing minus Admitted"],
        ["Morning Boys",           "Regular male admissions in morning shift"],
        ["Morning Girls",          "Regular female admissions in morning shift"],
        ["Evening Boys",           "Regular male admissions in evening shift"],
        ["Evening Girls",          "Regular female admissions in evening shift"],
        ["OOSC Boys",              "Out-of-School Children — male"],
        ["OOSC Girls",             "Out-of-School Children — female"],
        ["P2G Boys",               "P2G program admissions — male"],
        ["P2G Girls",              "P2G program admissions — female"],
        ["Matric Tech Count",      "New Matric Tech admissions (Class 9 & 10 only)"],
        ["Total Admitted",         "Grand total of all new admissions (all categories combined)"],
      ],
      [3000, 6360]
    ),
    gap(),
    body("Export: PDF (landscape, A4, generated on demand)"),
    gap(),

    h2("12.2  Sector Report"),
    body("Purpose: Provides a sector-level summary of all admission activity. Allows FDE to compare performance across sectors at a glance."),
    gap(),
    bullet("One row per sector showing: total seats, existing enrollment, total admitted, available seats"),
    bullet("Breakdown columns: OOSC total, P2G total, Matric Tech total per sector"),
    bullet("Today's admissions vs cumulative admissions per sector"),
    bullet("School count per sector"),
    bullet("Grand totals row at the bottom"),
    gap(),

    h2("12.3  Gender Report"),
    body("Purpose: Analyzes admissions by student gender across all schools and classes. Used for gender-equity monitoring."),
    gap(),
    bullet("Male vs Female admission counts compared side by side"),
    bullet("Per sector breakdown: boys schools total vs girls schools total"),
    bullet("Percentage split between male and female admissions"),
    bullet("Filterable by sector and school type"),
    gap(),

    h2("12.4  OOSC Report — Out-of-School Children"),
    body("Purpose: Tracks Out-of-School Children admissions specifically. One of FDE's key performance indicators."),
    gap(),
    bullet("School-by-school OOSC admission counts"),
    bullet("Columns: OOSC Boys (Morning), OOSC Girls (Morning), OOSC Boys (Evening), OOSC Girls (Evening), Total OOSC"),
    bullet("Sector-wise subtotals and grand total"),
    bullet("Filterable by sector, school type, and date range"),
    bullet("Highlights schools with zero OOSC admissions"),
    gap(),

    h2("12.5  Vacancy Report"),
    body("Purpose: Identifies schools that still have available seats. Used by FDE to understand remaining capacity and direct families to schools with openings."),
    gap(),
    bullet("School name, sector, type, gender"),
    bullet("Total Seats / Existing Enrollment / Admitted This Year / Available Seats"),
    bullet("Fill Percentage (how full the school is as a percentage)"),
    bullet("Color-coded: green (seats available), amber (nearly full ≥80%), red (full)"),
    bullet("Filterable by sector, school type, and vacancy status (has seats / nearly full / full)"),
    bullet("Also accessible to HOI scoped to their own school"),
    gap(),

    h2("12.6  Report Dashboard — Visual Charts"),
    body("Purpose: Visual overview of admission data using charts. Provides an at-a-glance picture of the current admission season's progress."),
    gap(),
    bullet("Admissions by Sector — bar chart comparing total admitted per sector"),
    bullet("Daily Admission Trend — line chart showing how many students were admitted each day"),
    bullet("Special Programs Breakdown — pie/donut chart: Regular vs OOSC vs P2G vs Matric Tech"),
    bullet("Summary cards: total admitted, available seats, schools submitted, schools not submitted"),
    gap(),

    h2("12.7  Schools Report"),
    body("Purpose: Overview of all schools with their configuration and submission status. Used by FDE Cell to track which schools are active and up-to-date."),
    gap(),
    bullet("School name, sector, type, gender, shift"),
    bullet("Classes configured status (yes/no)"),
    bullet("Baseline enrollment status (draft / submitted / verified / locked)"),
    bullet("Staff strength register status (draft / submitted / verified / locked)"),
    bullet("Total submissions this academic year"),
    bullet("Last submission date"),
    bullet("Filterable by sector, school type, and configuration status"),
    gap(),

    h2("12.8  College Reports — Model & Ex-FG Colleges"),
    body("Purpose: Dedicated admission summary for Model Colleges and Ex-FG Colleges, which operate differently from regular schools."),
    gap(),
    bullet("List of all Model Colleges (or Ex-FG Colleges) with admission totals"),
    bullet("Columns: College Name, Sector, UC, Total Boys Admitted, Total Girls Admitted, Total Admitted"),
    bullet("Class-wise breakdown on each college's profile page"),
    bullet("PDF export (A4 landscape) of the full college list with totals"),
    bullet("Filterable by sector, UC, and school name"),
    gap(),

    h2("12.9  Staff Strength Report — PDF & Excel"),
    body("Purpose: Export the full staff strength data across all schools for FDE reporting and record-keeping."),
    gap(),
    bullet("PDF export: formatted report of a single school's staff register with all post types and counts"),
    bullet("Excel export: full tabular export of all schools' staff strength entries for analysis"),
    bullet("Filterable by sector, school type, and register status (draft/submitted/verified/locked)"),
    bullet("Includes both Teaching Staff and Program Staff sections"),
    bullet("Columns match the register: Sanctioned Posts, Filled, Sacked, Daily Wagers In/Out, Study Leave, Deputationist In/Out, Temporary In/Out, Number of Posts"),
    gap(),

    h2("12.10  UC Control Rooms Report — PDF"),
    body("Purpose: Export the full UC Control Rooms directory as a landscape PDF for field reference."),
    gap(),
    bullet("Lists all Union Council control rooms with UC name, FDE school, organization, focal persons, NCHD FO contacts, FDE focal person contacts"),
    bullet("Filterable by UC name/code, focal person name, or organization before export"),
    bullet("Landscape A4 PDF format for easy printing and field distribution"),
  ];
}

// ── Module 13: Merit Lists ─────────────────────────────────────────────────────
function module13() {
  return [
    h1("Module 13 — Merit Lists"),
    greenLine(),
    body("Schools running merit-based admissions upload PDF/Excel merit list files. These appear on the Public Portal for parents to view and download."),
    gap(),
    h2("HOI Features"),
    bullet("Upload one or multiple merit list files (PDF, XLSX, CSV) for their school"),
    bullet("Add a title/label for each uploaded file"),
    bullet("Delete uploaded files when no longer needed"),
    h2("Public Portal Display"),
    bullet("A 'Merit List' badge appears on school cards in the portal when files are uploaded"),
    bullet("Dedicated Merit Lists page lists all schools with files and download buttons"),
    bullet("No login required — parents can access directly"),
  ];
}

// ── Module 14: Staff Management (expanded) ────────────────────────────────────
function module14() {
  return [
    h1("Module 14 — Staff Management"),
    greenLine(),
    body("Record and verify the full staff strength of every FDE school — teaching posts and program posts. Data is entered by HOI, verified by FDE Cell, and visible (read-only) to AEO and Director."),
    gap(),

    h2("14.1  HOI — Enter & Submit Staff Strength Register"),
    body("HOI fills in a staff strength register for the current academic year. A draft register is automatically created on first visit."),
    gap(),
    h3("Teaching Staff Section"),
    body("Post types are filtered based on the school's type (Primary, High, Higher Secondary, etc.) — HOI only sees post types applicable to their institution."),
    gap(),
    simpleTable(
      ["Column", "Description"],
      [
        ["Post Type",          "e.g., Principal, Vice Principal, SST, EST, PST, PET, etc."],
        ["Sanctioned Posts",   "Total number of officially authorized positions for this post type"],
        ["Filled Posts",       "Currently occupied positions (staff working today)"],
        ["Sacked Employees",   "Employees removed or dismissed from service"],
        ["Daily Wagers (In)",  "Daily wage staff added during this period"],
        ["Daily Wagers (Out)", "Daily wage staff who left during this period"],
        ["Study Leave",        "Staff currently on study leave"],
        ["Deputationist (In)", "Staff on deputation to this school from other schools"],
        ["Deputationist (Out)","Staff from this school deployed on deputation elsewhere"],
        ["Temporary (In)",     "Temporary staff added during this period"],
        ["Temporary (Out)",    "Temporary staff who have left"],
        ["Number of Posts",    "Effective working staff count (used for reporting)"],
      ],
      [3000, 6360]
    ),
    gap(),
    h3("Program Staff Section"),
    body("Same columns as Teaching Staff but for non-teaching, program-specific roles tied to special programs (ECE coordinators, Matric Tech instructors, etc.)."),
    gap(),
    h3("HOI Actions"),
    bullet("Save as Draft — can re-edit any time before submission"),
    bullet("Submit — sends the register to FDE Cell for review"),
    gap(),
    body("Status Flow: Draft → Submitted → Verified / Returned → Locked", { bold: true, color: GREEN }),
    gap(),

    h2("14.2  FDE Cell — Review, Verify, Lock, and Export"),
    body("FDE Cell sees all school staff registers in one list and can manage each one."),
    gap(),
    h3("List View"),
    bullet("All schools with their register status (draft / submitted / verified / returned / locked)"),
    bullet("Filters: by Sector, School Type, Register Status"),
    bullet("Sort by last updated date — newest submissions at top"),
    gap(),
    h3("Detail View (per school)"),
    bullet("Full register display with Teaching Staff and Program Staff sections"),
    bullet("All entries visible: sanctioned, filled, sacked, daily wagers, study leave, deputationist, temporary, number of posts"),
    bullet("Submitted by (name + timestamp) and locked by (name + timestamp) shown"),
    gap(),
    h3("FDE Actions"),
    bullet("Verify — marks the register as verified; HOI cannot edit further without unlock"),
    bullet("Return — sends the register back to HOI with a note for revision"),
    bullet("Lock — permanently locks the register; prevents any further changes"),
    bullet("Export PDF — generates a formatted PDF of the school's full staff register"),
    bullet("Export Excel — downloads all school staff data in tabular format for analysis"),
    gap(),

    h2("14.3  AEO / Director — View Staff Strength Stats"),
    body("AEO and Director can see staff strength data within their scope as part of monitoring."),
    gap(),
    bullet("Summary counts on dashboard: schools with submitted registers, verified registers, pending registers"),
    bullet("School monitoring page shows the staff register status for each school"),
    bullet("Drill-down to view any individual school's full staff register (read-only)"),
    bullet("AEO sees only schools in their assigned sector(s)"),
    bullet("Director sees all schools system-wide"),
    bullet("Neither AEO nor Director can verify, lock, or export — those actions belong to FDE Cell only"),
  ];
}

// ── Modules 15–32 ────────────────────────────────────────────────────────────
function modulesB() {
  return [
    // MOD 15
    h1("Module 15 — New Construction Rooms"),
    greenLine(),
    body("Track new classrooms added through construction projects and record how those rooms are allocated to classes."),
    gap(),
    simpleTable(
      ["Field", "Description"],
      [
        ["Rooms Total",           "Total new rooms constructed or under construction"],
        ["Rooms Allocated",       "Rooms that have been formally assigned to specific classes"],
        ["Construction Status",   "pending / near_completion / completed"],
        ["Notes",                 "Any relevant construction or allocation notes"],
      ],
      [3000, 6360]
    ),
    gap(),
    bullet("HOI allocates new rooms to specific classes once construction is complete"),
    bullet("FDE Cell records and manages all construction room entries system-wide"),
    bullet("Dashboard stats: schools with new rooms, total rooms, completed, near-completion, estimated capacity added (rooms x 40)"),

    // MOD 16
    h1("Module 16 — Admission Corrections"),
    greenLine(),
    body("Once a daily admission entry is submitted, it is locked for editing. If a HOI made an error, they must submit a Correction Request — they cannot edit directly."),
    gap(),
    h2("HOI Submits Correction"),
    bullet("Selects the date and class they want to correct"),
    bullet("System shows the current (old) values alongside the new input fields"),
    bullet("HOI enters new correct values and provides a reason for the change"),
    h2("FDE Cell Reviews"),
    bullet("Side-by-side comparison of Old Values vs New Values for every field"),
    bullet("Approve — daily_admissions record updated; existing_enrollment adjusted by net difference"),
    bullet("Reject — correction declined with an FDE note"),
    bullet("HOI receives an email notification with the FDE Cell's decision and note"),
    bullet("Every correction decision is recorded in the Audit Log with full before/after values"),

    // MOD 17
    h1("Module 17 — Admission Edit Grants"),
    greenLine(),
    body("When a HOI needs to edit a past entry that is already locked, FDE Cell must grant them a time-limited edit permission. Without this grant, the HOI must use the Correction Request workflow."),
    gap(),
    simpleTable(
      ["Status", "Meaning"],
      [
        ["active",  "Grant is live; HOI can edit the specified past dates until expiry"],
        ["expired", "Grant has passed its expiry datetime — auto-expired by the system"],
        ["revoked", "FDE Cell manually revoked the grant before expiry; edit access removed immediately"],
      ],
      [1800, 7560]
    ),
    gap(),
    bullet("FDE specifies: school, date range (from/to), expiry datetime, and reason"),
    bullet("FDE Cell can revoke any active grant at any time with a revocation reason"),
    bullet("All grant creations and revocations are recorded in the Audit Log"),

    // MOD 18
    h1("Module 18 — Seat Configuration (FDE Override)"),
    greenLine(),
    body("FDE Cell can view and override the seat counts that schools configured themselves. FDE has final authority over seat numbers."),
    gap(),
    bullet("Override total_seats for any class in any school"),
    bullet("Override recorded with: who made it, the reason, and exact timestamp"),
    bullet("Lock seats: prevents HOI from changing seat counts again"),
    bullet("Unlock seats: re-enables HOI editing when needed"),
    bullet("Summary stats: system total seats, count of locked vs unlocked schools"),

    // MOD 19
    h1("Module 19 — Enrollment Override"),
    greenLine(),
    body("FDE Cell can directly override a school's existing_enrollment on a per-class basis for class-level corrections that cannot be fixed via daily admission corrections."),
    gap(),
    bullet("Used when HOI baseline figures were wrong at class setup stage and already verified"),
    bullet("FDE selects school, views each class with current enrollment, seats, and available seats"),
    bullet("FDE updates existing_enrollment values directly"),
    bullet("FDE can also unlock a submitted/verified enrollment for revision"),

    // MOD 20
    h1("Module 20 — Admission Period Control"),
    greenLine(),
    body("FDE Cell controls when admissions are open, how long they run, and what the daily submission cutoff time is."),
    gap(),
    simpleTable(
      ["Action", "Effect"],
      [
        ["Open Admissions",  "Sets all active institutions to admission_status = open"],
        ["Close Admissions", "Sets all active institutions to admission_status = closed"],
        ["Update Period",    "Changes admission start/end dates and daily cutoff time"],
      ],
      [2800, 6560]
    ),
    gap(),
    body("Live stats on this page: total schools, schools submitted today, days elapsed, days remaining, whether admissions are currently open."),

    // MOD 21
    h1("Module 21 — Monitoring & School Tracking"),
    greenLine(),
    body("Allow supervisory roles to view the detailed admission history of any school, class by class, date by date."),
    gap(),
    bullet("School list: all schools in scope with seats, enrollment, admitted, available"),
    bullet("Color-coded submission status indicators"),
    bullet("School detail: class-wise seat configuration, cumulative admissions, daily log with statuses"),
    bullet("Non-submitting schools: live list of schools that have not submitted today (FDE dashboard)"),
    bullet("HOI sees only their own school; AEO sees their sector; Director and FDE see all schools"),

    // MOD 22
    h1("Module 22 — Colleges (Model & Ex-FG)"),
    greenLine(),
    body("Dedicated list and profile pages for Model Colleges and Ex-FG Colleges with admission statistics."),
    gap(),
    bullet("Model Colleges list: name, sector, UC, HOI, Total Boys / Girls / Total Admitted"),
    bullet("Ex-FG Colleges list: same layout for Ex-FG College type"),
    bullet("College profile page: full details + class-wise admission breakdown"),
    bullet("PDF export (landscape A4) of the full filtered college list"),

    // MOD 23
    h1("Module 23 — UC Control Rooms"),
    greenLine(),
    body("Track Union Council Control Rooms — each UC has a designated control room with specific focal persons, organizations, and FDE contacts."),
    gap(),
    simpleTable(
      ["Field", "Description"],
      [
        ["Union Council",          "Which UC this control room serves"],
        ["FDE School Name",        "FDE school hosting the control room"],
        ["Organization Name",      "Organization running it (e.g., NCHD)"],
        ["Focal Person Name/Phone","Contact at the control room"],
        ["NCHD FO Name/Phone",     "NCHD Field Officer name and contact"],
        ["FDE Focal Person",       "FDE's assigned focal person name and phone"],
        ["Notes",                  "Any additional notes"],
      ],
      [2800, 6560]
    ),
    gap(),
    bullet("Searchable by UC name/code, school name, focal person, NCHD FO"),
    bullet("Filter by organization; PDF export (landscape A4)"),

    // MOD 24
    h1("Module 24 — Audit Log"),
    greenLine(),
    body("A complete tamper-evident log of all significant actions taken in the system."),
    gap(),
    simpleTable(
      ["Field", "Description"],
      [
        ["Action",      "What was done (approved, rejected, updated, locked, etc.)"],
        ["Model",       "Which entity was affected (DailyAdmission, AdmissionCorrection, etc.)"],
        ["Old Values",  "Data before the change — stored as JSON"],
        ["New Values",  "Data after the change — stored as JSON"],
        ["Changed By",  "User name and their role at the time of change"],
        ["Institution", "Which school the change relates to"],
        ["Timestamp",   "Exact date and time of the action"],
      ],
      [2000, 7360]
    ),
    gap(),
    bullet("Filterable by user, role, field changed, institution, and date range"),
    bullet("Stats: total audit entries today / total entries in system"),
  ];
}

// ── Module 25: Public Portal (detailed) ──────────────────────────────────────
function module25() {
  return [
    h1("Module 25 — Public Portal"),
    greenLine(),
    body("No login required. Accessible to all parents, students, and the general public. The Public Portal is the parent-facing window into FDE's admission data — showing which schools have seats, what programs they offer, and where to find merit lists."),
    gap(),

    h2("25.1  School Search Page"),
    body("The main landing page of the portal. Parents can search and filter schools to find the right fit for their child."),
    gap(),
    h3("Search Filters Available"),
    bullet("School name or EMIS code — free text search"),
    bullet("Sector — filter to a specific geographic sector"),
    bullet("School Type — Primary / Elementary / Middle / High / Higher Secondary / Model College"),
    bullet("Gender — Boys / Girls / Co-education"),
    bullet("Shift — Morning / Evening / Both"),
    bullet("Special Filters: Has Transport / Has Meal Program / Has Matric Tech / Has Evening Classes / Is Cambridge School / Has ECE Center"),
    bullet("Class-specific availability — filter to schools that still have seats open in a specific class"),
    bullet("Vacancy status — Has Seats / Nearly Full (>=80%) / Full"),
    gap(),
    h3("Hero Stats (top of the page)"),
    bullet("Total FDE Schools — total active schools registered in the portal"),
    bullet("Schools with Available Seats — count of schools that still have capacity"),
    bullet("Total Available Seats System-wide — grand sum of all remaining seats"),
    bullet("Total Students Admitted This Year — cumulative admissions across all schools"),
    gap(),
    h3("School Cards Show"),
    bullet("School name, sector, type (badge), gender"),
    bullet("Program badges: ECE / Matric Tech / Cambridge / Transport / Meal Program / Evening Classes"),
    bullet("Merit List badge — shown if the school has uploaded merit list files"),
    bullet("Key stats: Total Seats / Existing Enrollment / Admitted This Year / Available Seats"),
    gap(),
    body("Default Behavior: Schools with zero available seats are hidden from the default view. Parents always see schools where seats are available unless they explicitly filter for 'Full' schools.", { bold: true }),
    gap(),

    h2("25.2  School Detail Page"),
    body("Clicking any school card opens the full school profile page — a detailed view of that school's seat availability by class."),
    gap(),
    h3("School Profile Section"),
    bullet("School name, sector, type, gender, and shift"),
    bullet("All special program badges (ECE, Matric Tech, Cambridge, Transport, Meal, Evening)"),
    bullet("Merit list download links if any files are uploaded"),
    gap(),
    h3("Class-Wise Seat Table"),
    bullet("One row per active class"),
    bullet("Columns: Class Name / Total Seats / Existing Enrollment / Admitted This Year / Available Seats"),
    bullet("For evening schools: Morning and Evening columns shown separately for each class"),
    bullet("Available seats shown in green (positive) or red (zero/negative)"),
    bullet("Total row at the bottom summing all classes"),
    gap(),

    h2("25.3  Seat Availability Page"),
    body("A dedicated page for parents who want to see available seats grouped by area — Urban, Rural, or Model Colleges."),
    gap(),
    h3("Area Cards (3 groups)"),
    simpleTable(
      ["Area", "Sectors Included"],
      [
        ["Urban Schools",  "Urban-I + Urban-II sectors"],
        ["Rural Schools",  "B-K, Tarnol, Sihala, Nilore sectors"],
        ["Model Colleges", "Model Colleges sector"],
      ],
      [2800, 6560]
    ),
    gap(),
    h3("Each Area Card Shows"),
    bullet("Total Seats — total authorized capacity across all schools in the area"),
    bullet("Existing Enrollment — pre-existing students (previous year)"),
    bullet("Available Seats — total remaining capacity (Morning + Evening combined)"),
    bullet("Morning Available and Evening Available breakdown"),
    gap(),
    h3("Per-Sector Detail Table (inside each card)"),
    bullet("Sub-table showing each individual sector within the area group"),
    bullet("Each sector row: sector name, total seats, available seats"),
    gap(),
    h3("Drill-Down"),
    bullet("Clicking an area (Urban / Rural / Model) shows a table of all schools in that area with available seats"),
    bullet("Each row: school name, sector, morning seats available, evening seats available, total available"),
    bullet("Only shows schools that still have seats — full schools are excluded from this view"),
    gap(),

    h2("25.4  Merit Lists Page"),
    body("A dedicated page listing all schools that have uploaded merit list files. Parents preparing for merit-based admissions can find and download these files here."),
    gap(),
    bullet("Lists every school with at least one uploaded merit list file"),
    bullet("Schools ordered alphabetically by name"),
    bullet("Each school card shows the school name, sector, and all available merit list files"),
    bullet("Each file shows: file title/label and a Download button"),
    bullet("Files are delivered directly as downloadable attachments"),
    bullet("Merit List badge appears on school cards in the school search page when files are available"),
    gap(),

    h2("25.5  Portal Settings (FDE Controlled)"),
    body("FDE Cell configures the public-facing content of the portal from within the admin interface."),
    gap(),
    bullet("Announcement Banner Text — shown at the top of the portal (e.g., 'Admissions are now open for 2026-27')"),
    bullet("Contact Email and Phone — displayed to parents on the portal for enquiries"),
    bullet("Portal Open/Closed toggle — can hide the entire portal if needed"),
    body("These settings do not require a system deployment or code change — FDE Cell can update them instantly from App Settings or Portal Settings.", { bold: true }),
  ];
}

// ── Modules 26–32 ─────────────────────────────────────────────────────────────
function modulesC() {
  return [
    h1("Module 26 — Announcements"),
    greenLine(),
    body("FDE Cell publishes system-wide announcements that appear inside the portal for specific roles or all users."),
    gap(),
    simpleTable(
      ["Field", "Description"],
      [
        ["Title",        "Short headline (up to 200 characters)"],
        ["Body",         "Full announcement text (up to 2,000 characters)"],
        ["Type",         "info / warning / success / danger — controls color and icon"],
        ["Priority",     "normal / high / urgent"],
        ["Is Active",    "Whether the announcement is currently live"],
        ["Is Pinned",    "Pinned announcements appear at the top of the list"],
        ["Published At", "Optional: schedule for a future publish date"],
        ["Expires At",   "Optional: auto-hide after this date/time"],
        ["Target Roles", "Which roles see this: HOI / AEO / FDE Cell / Director (blank = all roles)"],
      ],
      [2000, 7360]
    ),
    gap(),

    h1("Module 27 — Notifications"),
    greenLine(),
    body("In-app notification system that alerts users — primarily HOIs — about important events related to their school."),
    gap(),
    bullet("Notification bell in the top navigation bar showing unread count badge"),
    bullet("Full notifications list page (paginated, newest first)"),
    bullet("Mark single notification as read (AJAX supported — no page reload)"),
    bullet("Mark all notifications as read at once"),
    bullet("Delete individual notifications permanently"),
    bullet("Triggered by: correction approved/rejected, referral sent to school, system events"),
    bullet("All notifications marked as read automatically when HOI opens the notifications list page"),

    h1("Module 28 — AI Reports (AI Studio)"),
    greenLine(),
    body("FDE Cell can query admission data using natural language questions instead of navigating reports manually."),
    gap(),
    bullet("Type a plain-English question and receive an instant data-driven answer"),
    bullet("Results include tables, summaries, and comparisons"),
    bullet("Example: 'Which schools have not submitted today?'"),
    bullet("Example: 'How many OOSC students were admitted in Class 1 this month?'"),
    bullet("Example: 'Show me the top 10 schools by total admissions this year'"),
    bullet("Example: 'What is the total available capacity in Model Colleges?'"),

    h1("Module 29 — App Settings"),
    greenLine(),
    body("Configure the portal's branding, identity, and operational mode. All settings stored in the database — applied instantly without code changes."),
    gap(),
    simpleTable(
      ["Setting", "Description"],
      [
        ["App Name",           "Name shown in browser title bar and sidebar header"],
        ["App Tagline",        "Subtitle shown below the app name"],
        ["Sidebar Footer Text","Small text at the bottom of the sidebar navigation"],
        ["Primary Color",      "Main brand color (hex code) used throughout the UI"],
        ["Secondary Color",    "Accent color (hex code)"],
        ["Support Email",      "Contact email shown on help pages"],
        ["Support Phone",      "Contact phone shown on help pages"],
        ["App Logo",           "Upload custom logo (PNG, JPG, SVG, WebP — max 2MB)"],
        ["App Favicon",        "Upload custom browser favicon (PNG, ICO — max 512KB)"],
        ["Show Public Portal", "Toggle to show or hide the public-facing portal entirely"],
        ["Maintenance Mode",   "Toggle to put portal in maintenance mode for all non-admin users"],
        ["Maintenance Message","Custom message shown to users during maintenance"],
      ],
      [2800, 6560]
    ),

    h1("Module 30 — Theme & Appearance"),
    greenLine(),
    body("Fully customize the visual appearance of the entire portal — colors, typography, sidebar dimensions, and default mode — without touching code."),
    gap(),
    simpleTable(
      ["Category", "Settings"],
      [
        ["Colors",        "Primary, Secondary, Dark BG, Dark Card, Dark Sidebar, Light BG, Light Card, Light Sidebar, Active Text (Dark/Light)"],
        ["Layout",        "Sidebar Width, Sidebar Font Size, Sidebar Link Padding, Topbar Height, Topbar Font Size"],
        ["Cards",         "Card Border Radius, Small Radius, Card Padding"],
        ["Typography",    "Font Family (Inter/Roboto/Poppins/DM Sans/Nunito/Outfit), Base Font Size (12-18px)"],
        ["Default Mode",  "Dark or Light — the default mode all users see on first login"],
      ],
      [2000, 7360]
    ),
    gap(),
    bullet("Reset to Defaults button restores all theme settings to factory defaults instantly"),

    h1("Module 31 — Portal Settings"),
    greenLine(),
    body("Configure the content and status of the Public Portal from within the FDE admin interface."),
    gap(),
    bullet("Public Announcement Text — shown at top of portal landing page"),
    bullet("Contact Email and Phone — displayed to parents for enquiries"),
    bullet("Portal Open/Closed — toggle to show or hide the portal entirely"),

    h1("Module 32 — System Reset"),
    greenLine(),
    body("A nuclear option to completely wipe all system data and re-seed the database from scratch. Used at the end of an academic year to prepare for the next year's admission cycle."),
    gap(),
    body("WARNING: This action is IRREVERSIBLE. All data is permanently deleted. Only use at the start of a new academic year after all previous data has been exported and archived.", { bold: true, color: "C0392B" }),
    gap(),
    h2("What It Does"),
    bullet("Deletes all data from every table (admissions, corrections, transfers, referrals, staff registers, institutions, users, roles)"),
    bullet("Clears all Laravel caches (config, route, view, application cache)"),
    bullet("Re-runs all database seeders to restore base data (roles, classes, academic year, admin user, sectors, institutions)"),
    bullet("Logs out the current user — their session is wiped by the truncation"),
    h2("Safety Mechanism"),
    bullet("Requires typing exactly 'RESET SYSTEM' in a confirmation field before executing"),
    bullet("The action is fully logged (user name, IP address, timestamp) before execution begins"),
    bullet("Cannot be triggered accidentally — it is protected by both text confirmation and role permission"),
  ];
}

// ── Special Programs ───────────────────────────────────────────────────────────
function specialPrograms() {
  return [
    h1("Special Programs"),
    greenLine(),
    simpleTable(
      ["Program", "Flag", "Classes", "Extra Fields"],
      [
        ["OOSC — Out-of-School Children", "Always available", "All classes", "OOSC Boys / Girls (per shift) in daily form"],
        ["P2G — Path to Growth",          "Always available", "All classes", "P2G Boys / Girls (per shift) in daily form"],
        ["Matric Tech Program",           "has_matric_tech",  "Class 9 & 10 only", "Existing count (setup) + Daily count (admissions)"],
        ["ECE",                           "has_ece",          "ECE-I, ECE-II/Prep", "Full seat/enrollment setup + daily admissions"],
        ["Evening / Dual Shift",          "has_evening_classes","All classes", "Splits ALL fields into Morning + Evening everywhere"],
        ["Cambridge",                     "is_cambridge",     "N/A", "Badge on public portal; filterable in portal search"],
      ],
      [2200, 1800, 1900, 3460]
    ),
    gap(),
    h2("Matric Tech — Dashboard Display (All Roles)"),
    bullet("Matric Tech Existing / Prev. Year — previous year's Matric Tech students (from class setup)"),
    bullet("This Year / New Admits — sum of all daily matric_tech_count entries (with today's sub-count)"),
    bullet("Total / Combined — Existing + This Year"),
    gap(),
    h2("Dual-Shift Effect Across System"),
    simpleTable(
      ["Module", "Effect When Evening Classes = ON"],
      [
        ["Class Setup",           "All fields split: Morning Existing / Evening Existing / Morning Seats / Evening Seats"],
        ["Daily Admissions",      "Morning Boys/Girls + Evening Boys/Girls as separate input fields"],
        ["HOI Dashboard",         "Morning Admitted and Evening Admitted shown separately"],
        ["AEO / Director / FDE",  "Sub-labels show 'Morning X · Evening Y' on each school card"],
        ["Public Portal",         "Per-shift seat tables displayed on school detail page"],
        ["Master Report",         "Per-shift columns included in PDF export"],
      ],
      [2800, 6560]
    ),
  ];
}

// ── Dashboards ────────────────────────────────────────────────────────────────
function dashboards() {
  return [
    h1("Dashboard — HOI"),
    greenLine(),
    h2("Setup Checklist"),
    body("A visual progress indicator at the top showing which setup steps are complete (Profile / Facilities / Classes & Sections / Baseline Enrollment). Each step is a clickable link; incomplete steps are highlighted."),
    gap(),
    h2("Summary Cards"),
    simpleTable(
      ["Card", "Value"],
      [
        ["Intake Capacity",    "Total authorized seats across all active classes"],
        ["Existing Enrollment","Students already enrolled from previous year"],
        ["Newly Admitted",     "Total students admitted this academic year"],
        ["Seats Available",    "Total minus Existing minus Admitted"],
      ],
      [3000, 6360]
    ),
    gap(),
    body("For evening schools, each card shows 'Morning X · Evening Y' sub-labels."),
    gap(),
    h2("Matric Tech Cards (if applicable)"),
    bullet("Matric Tech Existing | Admitted This Year (with today sub-count) | Total Combined"),
    h2("Class-Wise Admission Table"),
    bullet("Every active class with: Existing, Intake Capacity, Newly Admitted, Available Seats"),
    bullet("Evening schools show Morning and Evening columns separately"),
    h2("Quick Actions"),
    bullet("Enter Today's Admissions | View Full Report | Edit Class Setup"),

    h1("Dashboard — AEO"),
    greenLine(),
    simpleTable(
      ["Card / Section", "Value"],
      [
        ["Grand Summary",     "Schools / Total Seats / Existing / Admitted This Year / Available / Total Enrollment"],
        ["Matric Tech",       "Existing / This Year (with today count) / Total"],
        ["Staff Strength",    "Schools submitted / verified / pending staff registers"],
        ["New Rooms",         "Total rooms / Allocated / Remaining / Schools with new rooms"],
        ["Sector Summary",    "One row per assigned sector with all key stats"],
        ["School-Wise Table", "Full table of every school with class-level breakdown and section counts"],
      ],
      [2800, 6560]
    ),

    h1("Dashboard — Director"),
    greenLine(),
    body("Identical layout to the AEO Dashboard but scoped to ALL sectors system-wide. All data is read-only — no edit or action buttons appear anywhere."),
    gap(),
    bullet("All schools across all sectors aggregated"),
    bullet("Per-sector breakdown table"),
    bullet("Matric Tech system-wide totals"),
    bullet("Staff Strength status overview"),
    bullet("New construction rooms system-wide"),

    h1("Dashboard — FDE Cell"),
    greenLine(),
    h2("Today's Stats (5 cards)"),
    simpleTable(
      ["Card", "Value"],
      [
        ["Total Today",      "All students admitted today (all programs combined)"],
        ["Regular (Today)",  "Morning + Evening regular admissions today"],
        ["OOSC (Today)",     "Out-of-School Children admitted today"],
        ["P2G (Today)",      "P2G program admissions today"],
        ["Matric Tech Today","Matric Tech admissions today"],
      ],
      [3000, 6360]
    ),
    gap(),
    h2("Cumulative Stats Row"),
    body("Same 5 categories but for the entire academic year (not just today)."),
    h2("Matric Tech Breakdown Row"),
    bullet("Matric Tech Existing (prev. year baseline) | Admitted This Year (with today count) | Total"),
    h2("School Submission Status"),
    bullet("Total Schools / Submitted Today / Not Submitted"),
    h2("Sector-Wise Breakdown Table"),
    bullet("Per sector: cumulative total / today total / OOSC / P2G / Matric Tech counts"),
    h2("Schools Not Submitted Today"),
    bullet("Full list of non-submitting schools grouped by sector — for immediate follow-up"),
    h2("Additional Sections"),
    bullet("Available Capacity: Total Seats minus Existing minus Total Admitted (system-wide)"),
    bullet("New Construction Rooms: schools with rooms / total / completed / near-completion / estimated capacity"),
    bullet("Referral Stats: Total / Pending / Accepted / Rejected / Admitted / Not Admitted / Test Failed"),
  ];
}

// ── Data Flow ─────────────────────────────────────────────────────────────────
function dataFlow() {
  return [
    h1("Data Flow — End-to-End Summary"),
    greenLine(),
    h2("Phase 1 — Admin Setup"),
    bullet("Admin creates Sectors, Union Councils, and Schools (Institutions)"),
    bullet("Admin creates User Accounts and assigns roles (HOI, AEO, FDE Cell, Director)"),
    bullet("Admin creates Academic Year and sets it active with admission dates and cutoff time"),
    gap(),
    h2("Phase 2 — HOI Setup Sequence"),
    bullet("Profile Setup: links HOI account to school, confirms gender and shift"),
    bullet("Facilities Setup: declares programs (Evening, ECE, Matric Tech, transport, etc.)"),
    bullet("Class Setup: configures active classes, sections, seat counts, existing enrollment, Matric Tech existing count"),
    bullet("Baseline Enrollment: submits promoted/failed breakdown — FDE Cell verifies"),
    bullet("Admission Quota: (optional) sets per-class intake targets"),
    bullet("Staff Strength: enters teaching and program staff data — FDE Cell verifies and locks"),
    gap(),
    h2("Phase 3 — Daily Admission Cycle (Every Working Day)"),
    bullet("HOI opens daily form — sees classes with live seat capacity"),
    bullet("Enters: Morning/Evening x Boys/Girls x Regular / OOSC / P2G / Matric Tech per class"),
    bullet("System validates capacity in real time — blocks over-admission"),
    bullet("HOI saves (draft) then submits — AEO and FDE Cell can see data immediately"),
    gap(),
    h2("Phase 4 — Monitoring & Management (Continuous)"),
    bullet("AEO monitors sector schools live on their dashboard"),
    bullet("FDE Cell monitors all schools, identifies non-submitters, follows up"),
    bullet("FDE Cell publishes Announcements to notify all HOIs of deadlines or policy changes"),
    bullet("HOI submits Correction Requests for past errors — FDE reviews and HOI is emailed the decision"),
    bullet("FDE Cell issues Edit Grants for specific past-entry edits when needed"),
    bullet("FDE Cell creates Student Transfers — enrollment counts updated automatically"),
    bullet("FDE Cell issues Student Referrals — HOI acts on them within the portal"),
    bullet("FDE Cell overrides seats or enrollment if needed — all actions recorded in Audit Log"),
    bullet("FDE Cell verifies and locks Staff Strength registers for each school"),
    gap(),
    h2("Phase 5 — Reporting (On Demand)"),
    bullet("HOI: Admission Report + Vacancy Report (own school only)"),
    bullet("FDE: Master Report / Sector / Gender / OOSC / Vacancy / Schools Reports"),
    bullet("FDE: Staff Strength export (PDF + Excel) for all schools"),
    bullet("FDE: College lists and profiles export (PDF)"),
    bullet("FDE: UC Control Rooms PDF export"),
    bullet("FDE: AI Studio natural-language data queries"),
    bullet("All major reports available as PDF and/or Excel export"),
    gap(),
    h2("Phase 6 — Public Portal (Always Live)"),
    bullet("Parents search schools by sector, type, gender, shift, and special programs"),
    bullet("School cards show available seats updated in real time"),
    bullet("School detail pages show per-class (and per-shift) seat data"),
    bullet("Seat Availability page: Urban / Rural / Model Colleges breakdown"),
    bullet("Merit Lists page for schools with uploaded PDF merit list files"),
  ];
}

// ═══════════════════════════════════════════════════════════════════════════════
//  BUILD DOCUMENT
// ═══════════════════════════════════════════════════════════════════════════════

const SKILL_DIR = "C:/Users/Malaz/AppData/Roaming/Claude/local-agent-mode-sessions/skills-plugin/cd62b215-d943-420d-a03a-41f7da545681/29913fe8-c519-4467-ac35-ab49f4cce199/skills/docx";

const doc = new Document({
  numbering: {
    config: [{
      reference: "bullets",
      levels: [{ level: 0, format: LevelFormat.BULLET, text: "•",
        alignment: AlignmentType.LEFT,
        style: { paragraph: { indent: { left: 720, hanging: 360 } } } }]
    }]
  },
  styles: {
    default: { document: { run: { font: "Arial", size: 22, color: DARK } } },
    paragraphStyles: [
      { id: "Heading1", name: "Heading 1", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 36, bold: true, font: "Arial", color: GREEN },
        paragraph: { spacing: { before: 240, after: 160 }, outlineLevel: 0 } },
      { id: "Heading2", name: "Heading 2", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 28, bold: true, font: "Arial", color: GREEN2 },
        paragraph: { spacing: { before: 180, after: 120 }, outlineLevel: 1 } },
      { id: "Heading3", name: "Heading 3", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 24, bold: true, font: "Arial", color: TEAL },
        paragraph: { spacing: { before: 140, after: 80 }, outlineLevel: 2 } },
    ]
  },
  sections: [{
    properties: {
      page: {
        size: { width: 12240, height: 15840 },
        margin: { top: 1440, right: 1080, bottom: 1440, left: 1080 }
      }
    },
    headers: {
      default: new Header({
        children: [
          new Paragraph({
            border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: GREEN, space: 1 } },
            spacing: { after: 120 },
            children: [
              new TextRun({ text: "FDE Admission Portal 2026", bold: true, size: 20, color: GREEN, font: "Arial" }),
              new TextRun({ text: "  —  Federal Directorate of Education", size: 20, color: GRAY, font: "Arial" }),
            ]
          }),
        ]
      })
    },
    footers: {
      default: new Footer({
        children: [
          new Paragraph({
            border: { top: { style: BorderStyle.SINGLE, size: 6, color: GREEN, space: 1 } },
            spacing: { before: 120 },
            tabStops: [{ type: TabStopType.RIGHT, position: TabStopPosition.MAX }],
            children: [
              new TextRun({ text: "Design & Developed By  —  InnovaMaven", size: 18, color: GRAY, font: "Arial" }),
              new TextRun({ text: "\t", size: 18, font: "Arial" }),
              new TextRun({ text: "Page ", size: 18, color: GRAY, font: "Arial" }),
              new TextRun({ children: [PageNumber.CURRENT], size: 18, bold: true, color: GREEN, font: "Arial" }),
            ]
          })
        ]
      })
    },
    children: [
      ...coverPage(),
      ...tocPage(),
      ...section1(),
      ...section2(),
      ...section3(),
      ...section4(),
      ...section5to9(),
      ...modulesA(),
      ...module12(),
      ...module13(),
      ...module14(),
      ...modulesB(),
      ...module25(),
      ...modulesC(),
      ...specialPrograms(),
      ...dashboards(),
      ...dataFlow(),
      gap(200),
      greenLine(),
      new Paragraph({ alignment: AlignmentType.CENTER, spacing: { before: 200 }, children: [
        new TextRun({ text: "FDE Admission Portal 2026–27  |  Complete System Documentation  |  Version 3.0  |  April 2026", size: 18, color: GRAY, italic: true, font: "Arial" })
      ]}),
      new Paragraph({ alignment: AlignmentType.CENTER, children: [
        new TextRun({ text: "Design & Developed By InnovaMaven  |  Federal Directorate of Education, Islamabad", size: 18, color: GREEN, font: "Arial" })
      ]}),
    ]
  }]
});

Packer.toBuffer(doc).then(buffer => {
  fs.writeFileSync("C:/laragon/www/fde-admission-portal-2026/FDE_Admission_Portal_Documentation.docx", buffer);
  console.log("Document generated successfully.");
}).catch(err => {
  console.error("Error:", err);
  process.exit(1);
});
