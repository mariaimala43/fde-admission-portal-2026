// ════════════════════════════════════════════════════════════════════
//  FDE AI Report Agent Studio  —  public/js/ai_agent_studio.jsx
//
//  Schema-aware system prompts built from:
//    DailyAdmission: morning_boys, morning_girls, evening_boys,
//                    evening_girls, oosc_boys, oosc_girls,
//                    p2p_boys, p2p_girls
//    InstitutionClass: total_seats, existing_enrollment
//    NewConstructionRoom: construction_status, rooms_total,
//                         rooms_allocated
//    Classes: order, level
//    fill_rate = (existing + regular) / seats * 100
//    remaining = seats - existing - regular  (regular = shift cols only)
// ════════════════════════════════════════════════════════════════════

const { useState, useRef, useEffect, useCallback } = React;

const CTX = window.FDE_CONTEXT || {
  academicYear: "2026–27",
  csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || "",
  apiBase: "/fde/api",
  user: { name: "FDE User", role: "fde_cell" },
};

// ─── Colour helper ────────────────────────────────────────────────────
const rgba = (hex, a = 1) => {
  const r = parseInt(hex.slice(1,3),16), g = parseInt(hex.slice(3,5),16), b = parseInt(hex.slice(5,7),16);
  return `rgba(${r},${g},${b},${a})`;
};

// ─── Icon ─────────────────────────────────────────────────────────────
const P = {
  plus:    "M12 4v16m8-8H4",
  trash:   "M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16",
  edit:    "M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z",
  copy:    "M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z",
  print:   "M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z",
  send:    "M12 19l9 2-9-18-9 18 9-2zm0 0v-8",
  eye:     "M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z",
  x:       "M6 18L18 6M6 6l12 12",
  refresh: "M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15",
  menu:    "M4 6h16M4 12h16M4 18h16",
  warn:    "M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z",
};
const Icon = ({ name, size=16, className="" }) => (
  <svg width={size} height={size} viewBox="0 0 24 24" fill="none"
    stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round"
    className={className}><path d={P[name]}/></svg>
);

// ─── Shared HTML output rules (injected into every agent) ─────────────
const HTML_RULES = `
## STRICT HTML OUTPUT RULES
CRITICAL: Return ONLY raw HTML. Start your response with <div and end with </div>.
Do NOT include any explanation, preamble, markdown, or code fences like \`\`\`html.
Your entire response must be valid HTML that can be directly injected into a page.

### Report Layout (always this order):
1. HEADER: rounded-t-2xl p-5 text-white (bg = agent accent color) — show agent name, query title, academic year, generated time
2. KPI CARDS: grid grid-cols-2 md:grid-cols-4 gap-3 p-4 inside bg-white
3. MAIN TABLE: w-full text-sm (thead: bg-slate-50 text-xs uppercase tracking-wider text-slate-500 sticky top-0)
4. GRAND TOTAL ROW: bg-blue-50 font-bold border-t-2 border-blue-300 text-blue-900
5. FOOTER: mt-4 pt-3 border-t text-xs text-center text-gray-400 — "FDE Admission Portal · {agent} · {date}"

### Tailwind classes:
- Table wrapper: overflow-x-auto shadow-sm rounded-b-2xl border border-slate-200
- Rows: border-b border-slate-100 hover:bg-slate-50 transition-colors
- Numbers: font-mono tabular-nums text-right
- KPI card: bg-white rounded-xl p-4 shadow-sm border border-slate-100

### Number formatting: always use commas (1,234) — zero = "—"

### Status badges (inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold):
- Good/Completed: bg-green-100 text-green-800
- Warning/Near:   bg-yellow-100 text-yellow-800
- Critical/Full:  bg-red-100 text-red-800
- Neutral/Info:   bg-blue-100 text-blue-800

### Fill rate colours (apply to fill_rate % value):
- <70%:  class="text-green-600 font-medium"
- 70–89%: class="text-yellow-600 font-medium"
- ≥90%:  class="text-red-600 font-bold"

### Vacancy colours:
- remaining > 10: class="text-green-600"
- remaining 1–10: class="text-yellow-600 font-medium"
- remaining ≤ 0:  class="text-red-600 font-bold" + badge "FULL"

### CRITICAL SCHEMA RULES:
- fill_rate   = (existing + regular) / seats × 100  (regular = shift cols, NOT display total)
- remaining   = seats - existing - regular
- admitted    = regular + oosc + p2p  (display total only — do NOT use for vacancy)
- OOSC + P2P are analytics-only: never subtract from seats
- Partial school name match OK: "G-6/3" → matches "GGPS G-6/3"
- Class aliases: "KG"=KG, "class 1"=Gr-1, "Gr-1 to Gr-5"= filter by name
`;

// ─── Default agents ───────────────────────────────────────────────────
const DEFAULT_AGENTS = [
  {
    id: "master",
    name: "Master Report",
    icon: "📋",
    color: "#1d4ed8",
    description: "Full class-wise master report — seats, existing, admissions, OOSC, P2P, vacancy for all schools",
    dataSources: ["summary","master","grand_master","sectors","schools"],
    reportStyle: "detailed",
    systemPromptExtra: `You generate the FDE Master Report — the primary system-wide report.
Data key: "master" = array of class rows with: class, order, school_count, total_seats, total_existing, total_regular, total_oosc, total_p2p, total_admitted, total_filled, total_remaining, fill_rate.
Data key: "grand_master" = system totals.

Default table columns: Class | Schools | Seats | Existing | Regular Adm | OOSC | P2P | Total Admitted | Total Filled | Remaining | Fill%
Always show grand totals row from grand_master.
For sector-filtered reports use "schools" array filtered by sector.
For school-level drill-down show each institution's class breakdown.`,
    quickPrompts: [
      "Full master report — all classes, all schools",
      "Master report for Primary schools only",
      "Master report — G-6 sector schools",
      "Top 10 classes by fill rate system-wide",
      "Classes with remaining seats > 50 — opportunity report",
    ],
  },
  {
    id: "admissions",
    name: "Admissions Agent",
    icon: "📚",
    color: "#3b82f6",
    description: "Daily & cumulative admissions by class, school, sector with boys/girls/OOSC/P2P split",
    dataSources: ["summary","schools","by_class","daily","sectors"],
    reportStyle: "detailed",
    systemPromptExtra: `Focus on admission data.
Key fields: admitted (display total = regular+oosc+p2p), regular (seat-affecting), boys, girls, oosc, p2p.
For daily trend: use "daily" array with date, label, total, boys, girls, oosc, p2p.
Show subtotals per school, grand totals row.
For multi-class queries filter by_class array by class name.
For sector queries aggregate schools array by sector field.`,
    quickPrompts: [
      "Today's admissions — boys vs girls breakdown",
      "Top 10 schools by total admissions this year",
      "Sector-wise cumulative admissions with OOSC and P2P",
      "Daily trend — last 7 days with boys/girls split",
      "Class-wise admission summary — all classes ranked",
    ],
  },
  {
    id: "vacancy",
    name: "Vacancy Agent",
    icon: "🪑",
    color: "#10b981",
    description: "Seat capacity, existing enrollment, regular admissions, remaining vacancy and fill rates",
    dataSources: ["summary","schools","by_class","sectors"],
    reportStyle: "summary",
    systemPromptExtra: `Focus on capacity and vacancy.
CRITICAL: vacancy uses REGULAR admissions only (not oosc/p2p).
remaining = seats - existing - regular
fill_rate = (existing + regular) / seats * 100

Color rules: fill_rate <70% green, 70-89% yellow, ≥90% red bold.
Always add a "Schools at Risk" section listing schools where fill_rate ≥ 90%.
Show: School | Sector | Type | Seats | Existing | Admitted | Remaining | Fill%`,
    quickPrompts: [
      "Schools at 90%+ capacity — urgent at-risk list",
      "Sector-wise vacancy summary with fill rates",
      "Schools with most remaining seats available",
      "Class-wise vacancy — which classes are filling up",
      "Compare vacancy: Girls schools vs Boys schools",
    ],
  },
  {
    id: "oosc",
    name: "OOSC & P2P Agent",
    icon: "🎒",
    color: "#8b5cf6",
    description: "Out-of-school children and Private-to-Public admission tracking by sector, school, class",
    dataSources: ["summary","schools","by_class","sectors"],
    reportStyle: "detailed",
    systemPromptExtra: `Focus on OOSC (out-of-school children) and P2P (private-to-public) data.
Calculate per row: oosc_percent = round(oosc / admitted * 100)% — highlight >20% in amber badge.
Always show boys/girls split for OOSC.
Show: School | Sector | Regular | OOSC Boys | OOSC Girls | OOSC Total | OOSC% | P2P Boys | P2P Girls | P2P Total | Total Admitted
Grand total row must show system-wide OOSC% and P2P%.`,
    quickPrompts: [
      "OOSC and P2P breakdown — all sectors",
      "Schools with highest OOSC admission rate (>20%)",
      "Class-wise OOSC breakdown with boys/girls split",
      "P2P admissions — sector-wise ranking",
      "OOSC comparison: Primary vs Middle vs High schools",
    ],
  },
  {
    id: "gender",
    name: "Gender Parity Agent",
    icon: "⚖️",
    color: "#ec4899",
    description: "Gender balance analysis — boys vs girls per school, class, sector with parity index",
    dataSources: ["summary","schools","by_class","sectors"],
    reportStyle: "summary",
    systemPromptExtra: `Focus on gender data.
Calculate: girls_percent = round(girls / (boys + girls) * 100)%.
Parity status (badge):
  - "Balanced" green: girls 40–60%
  - "Boys-Heavy" blue: girls <40%
  - "Girls-Heavy" pink: girls >60%
Flag extreme imbalance (>70% one gender) with warning icon ⚠️.
Show: School | Sector | Boys | Girls | Total | Girls% | Parity Status
System summary cards: total boys, total girls, girls%, parity status.`,
    quickPrompts: [
      "Gender parity report — all sectors ranked",
      "Schools with extreme imbalance (>70% one gender)",
      "Class-wise boys vs girls split system-wide",
      "Girls enrollment: Primary vs Middle vs High comparison",
      "Sector-wise gender parity index",
    ],
  },
  {
    id: "rooms",
    name: "Rooms & Seats Agent",
    icon: "🏗️",
    color: "#f59e0b",
    description: "New construction rooms — allocation status, seats added, and per-class breakdown",
    dataSources: ["rooms","allocations","summary"],
    reportStyle: "executive",
    systemPromptExtra: `Focus on new construction rooms data.
"rooms" array fields: school, sector, rooms_total, rooms_allocated, unallocated, seats_added, status, status_label, is_fully_allocated.
"allocations" array fields: school, sector, class, rooms, seats_added, status.

Status badges: completed=green, near_completion=yellow, pending/other=gray.
Unallocated rooms: show with ⚠️ warning and red text if unallocated > 0.
seats_added = rooms_allocated × 40 (standard 40 seats per room).
Show summary cards: total schools, total rooms, allocated rooms, unallocated, total seats added.
Default table: School | Sector | Rooms Total | Allocated | Unallocated | Seats Added | Status`,
    quickPrompts: [
      "All schools with new rooms — allocation status overview",
      "Schools with unallocated rooms — urgent action needed",
      "Room allocation per class — which classes got new rooms",
      "Total new seats added from construction this year",
      "Completed vs near-completion construction status",
    ],
  },
  {
    id: "combined",
    name: "Intelligence Hub",
    icon: "🔗",
    color: "#06b6d4",
    description: "Cross-dataset executive reports — combine master, rooms, admissions, vacancy in one view",
    dataSources: ["summary","sectors","schools","by_class","daily","rooms","allocations","master","grand_master"],
    reportStyle: "executive",
    systemPromptExtra: `You have ALL datasets. Join them by school name or sector.
For executive reports: show 4–5 KPI headline cards first, then supporting table.
Common joins:
  - rooms + schools → new-room schools and their admission performance
  - master + sectors → sector drilldown from system level
  - by_class + rooms/allocations → class-level rooms built vs admissions
Always include a system health card: total schools, fill rate, OOSC%, rooms built.`,
    quickPrompts: [
      "Executive dashboard — all key KPIs in one view",
      "New-room schools: rooms built + admissions + vacancy combined",
      "Sector performance ranking — best to worst",
      "Schools at risk: high fill rate AND low OOSC = missed targets",
      "Complete system health report — all metrics",
    ],
  },
];

// ─── Build system prompt ──────────────────────────────────────────────
const buildSystemPrompt = (agent, liveData) => {
  const dataSection = liveData
    ? `## LIVE DATA FROM DATABASE
Academic Year: ${liveData.academic_year}
Generated at: ${liveData.generated_at}

### Summary KPIs
${JSON.stringify(liveData.summary, null, 2)}

### Sectors (${liveData.sectors?.length ?? 0})
${JSON.stringify(liveData.sectors, null, 2)}

### Schools (${liveData.schools?.length ?? 0} active, configured)
${JSON.stringify(liveData.schools, null, 2)}

### Class-wise System Totals
${JSON.stringify(liveData.by_class, null, 2)}

### Daily Trend (last 30 days)
${JSON.stringify(liveData.daily, null, 2)}

### Master Report — Class × System Matrix
${JSON.stringify(liveData.master, null, 2)}

### Master Grand Totals
${JSON.stringify(liveData.grand_master, null, 2)}

### New Construction Rooms
${JSON.stringify(liveData.rooms, null, 2)}

### Room Allocations (per class)
${JSON.stringify(liveData.allocations, null, 2)}

### Schema Notes
${JSON.stringify(liveData._schema_notes, null, 2)}`
    : `## DATA STATUS: Loading from database...`;

  return `You are the "${agent.name}" — a specialised AI report generator for the FDE (Federal Directorate of Education) Admission Portal, Islamabad, Pakistan.

Academic Year: ${CTX.academicYear}
User: ${CTX.user.name} (${CTX.user.role})
Focus: ${agent.description}
Report style: ${agent.reportStyle}

## AGENT INSTRUCTIONS
${agent.systemPromptExtra}

${HTML_RULES}

${dataSection}`;
};

// ─── API call via Laravel proxy ──────────────────────────────────────
// POST /fde/api/ai-generate → AiAgentDataController@generate
// API key stays server-side, never exposed to browser.
const callAI = async (agent, messages, liveData) => {
  const res = await fetch(`${CTX.apiBase}/ai-generate`, {
    method: "POST",
    credentials: "same-origin",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": CTX.csrfToken,
      "Accept": "application/json",
    },
    body: JSON.stringify({
      system: buildSystemPrompt(agent, liveData),
      messages: messages.map(m => ({ role: m.role, content: m.content })),
      agent_id: agent.id,
    }),
  });
  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.message || `HTTP ${res.status}`);
  }
  const data = await res.json();
  return data.content || "<p class='p-6 text-center text-red-500'>No report generated. Try rephrasing.</p>";
};

// ─── Live data hook ───────────────────────────────────────────────────
function useLiveData() {
  const [data, setData]       = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError]     = useState(null);

  const load = useCallback(async () => {
    setLoading(true); setError(null);
    try {
      const res = await fetch(`${CTX.apiBase}/agent-data`, {
        credentials: "same-origin",
        headers: { Accept: "application/json", "X-CSRF-TOKEN": CTX.csrfToken },
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      setData(await res.json());
    } catch(e) { setError(e.message); }
    finally { setLoading(false); }
  }, []);

  useEffect(() => { load(); }, [load]);
  return { data, loading, error, reload: load };
}

// ─── Data pill ────────────────────────────────────────────────────────
function DataPill({ loading, error, data, onReload }) {
  if (loading) return (
    <div className="flex items-center gap-1.5 px-2.5 py-1 bg-blue-900/30 border border-blue-800/40 rounded-full text-[10px] text-blue-300">
      <div className="w-2.5 h-2.5 border-2 border-blue-400 border-t-transparent rounded-full animate-spin"/>
      Loading live data…
    </div>
  );
  if (error) return (
    <div className="flex items-center gap-1.5 px-2.5 py-1 bg-red-900/30 border border-red-800/40 rounded-full text-[10px] text-red-300">
      <Icon name="warn" size={10}/> Error
      <button onClick={onReload} className="underline ml-0.5">retry</button>
    </div>
  );
  if (data) return (
    <div className="flex items-center gap-1.5 px-2.5 py-1 bg-green-900/20 border border-green-800/30 rounded-full text-[10px] text-green-400">
      <div className="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"/>
      Live · {data.schools?.length ?? 0} schools · {new Date(data.generated_at).toLocaleTimeString([], {hour:"2-digit",minute:"2-digit"})}
      <button onClick={onReload} className="ml-0.5 hover:text-green-200 transition">
        <Icon name="refresh" size={9}/>
      </button>
    </div>
  );
  return null;
}

// ─── Agent Card ───────────────────────────────────────────────────────
function AgentCard({ agent, active, onClick, onEdit, onDelete, onDuplicate }) {
  const [menu, setMenu] = useState(false);
  return (
    <div onClick={onClick} onMouseLeave={() => setMenu(false)}
      className="relative group cursor-pointer rounded-xl p-3 transition-all duration-150 select-none mb-0.5"
      style={{ background: active ? rgba(agent.color,0.15) : "transparent", border: `1px solid ${active ? rgba(agent.color,0.4) : "transparent"}` }}>
      <div className="flex items-center gap-2.5">
        <div className="w-8 h-8 rounded-lg flex items-center justify-center text-base shrink-0"
          style={{ background: rgba(agent.color, active ? 0.3 : 0.15) }}>
          {agent.icon}
        </div>
        <div className="flex-1 min-w-0">
          <p className="text-xs font-semibold text-slate-200 truncate">{agent.name}</p>
          <p className="text-[10px] text-slate-500 truncate mt-0.5">{agent.description.slice(0,44)}…</p>
        </div>
        <button onClick={e => { e.stopPropagation(); setMenu(v => !v); }}
          className="opacity-0 group-hover:opacity-100 p-1 rounded hover:bg-slate-700 text-slate-400 shrink-0">
          <svg width={13} height={13} viewBox="0 0 24 24" fill="currentColor">
            <circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/>
          </svg>
        </button>
      </div>
      {menu && (
        <div className="absolute right-2 top-9 z-50 bg-slate-800 border border-slate-700 rounded-xl shadow-2xl py-1 min-w-[150px]"
          onClick={e => e.stopPropagation()}>
          <button onClick={() => { onEdit(); setMenu(false); }} className="w-full flex items-center gap-2 px-3 py-2 text-xs text-slate-300 hover:bg-slate-700"><Icon name="edit" size={12}/> Edit Agent</button>
          <button onClick={() => { onDuplicate(); setMenu(false); }} className="w-full flex items-center gap-2 px-3 py-2 text-xs text-slate-300 hover:bg-slate-700"><Icon name="copy" size={12}/> Duplicate</button>
          <div className="border-t border-slate-700 my-1"/>
          <button onClick={() => { onDelete(); setMenu(false); }} className="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-400 hover:bg-slate-700"><Icon name="trash" size={12}/> Delete</button>
        </div>
      )}
    </div>
  );
}

// ─── Agent Builder Modal ──────────────────────────────────────────────
function AgentBuilder({ agent, onSave, onCancel }) {
  const [form, setForm] = useState(agent || {
    id: Date.now().toString(), name: "", icon: "🤖", color: "#6366f1",
    description: "", dataSources: ["schools"], reportStyle: "detailed",
    systemPromptExtra: "", quickPrompts: [""],
  });
  const DS = ["summary","sectors","schools","by_class","daily","rooms","allocations","master","grand_master"];
  const ICONS = ["🤖","📊","📋","🏗️","📚","🪑","🎒","⚖️","🔗","🏫","📈","🔍","⚡","🎯","📌","🧮","🗂️","📑"];
  const COLORS = ["#1d4ed8","#3b82f6","#10b981","#f59e0b","#ef4444","#8b5cf6","#ec4899","#06b6d4","#f97316","#6366f1","#14b8a6","#84cc16"];

  const set = (k,v) => setForm(f => ({...f,[k]:v}));
  const setP = (i,v) => setForm(f => { const p=[...f.quickPrompts]; p[i]=v; return {...f,quickPrompts:p}; });

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
      <div className="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-2xl max-h-[92vh] overflow-y-auto shadow-2xl">
        <div className="sticky top-0 flex items-center justify-between px-6 py-4 border-b border-slate-800 bg-slate-900 rounded-t-2xl z-10">
          <div className="flex items-center gap-3">
            <div className="w-8 h-8 rounded-lg flex items-center justify-center text-lg" style={{background:rgba(form.color,0.2)}}>{form.icon}</div>
            <h2 className="text-sm font-bold text-white">{agent ? "Edit Agent" : "Create New Agent"}</h2>
          </div>
          <button onClick={onCancel} className="p-1.5 rounded-lg hover:bg-slate-800 text-slate-400"><Icon name="x" size={15}/></button>
        </div>
        <div className="p-6 space-y-5">
          <div className="flex gap-4">
            <div className="shrink-0">
              <label className="block text-xs text-slate-500 mb-1.5 font-medium">Icon</label>
              <div className="flex flex-wrap gap-1.5 w-36">
                {ICONS.map(ic => (
                  <button key={ic} onClick={() => set("icon",ic)}
                    className={`w-8 h-8 rounded-lg text-base flex items-center justify-center transition ${form.icon===ic?"ring-2 ring-white bg-slate-700":"hover:bg-slate-800"}`}>{ic}</button>
                ))}
              </div>
            </div>
            <div className="flex-1 space-y-4">
              <div>
                <label className="block text-xs text-slate-500 mb-1 font-medium">Agent Name *</label>
                <input value={form.name} onChange={e => set("name",e.target.value)} placeholder="e.g. Sector Vacancy Monitor"
                  className="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500"/>
              </div>
              <div>
                <label className="block text-xs text-slate-500 mb-1.5 font-medium">Accent Color</label>
                <div className="flex gap-1.5 flex-wrap items-center">
                  {COLORS.map(c => (
                    <button key={c} onClick={() => set("color",c)} className="w-6 h-6 rounded-full transition hover:scale-110"
                      style={{background:c, outline:form.color===c?"2px solid white":"none", outlineOffset:2}}/>
                  ))}
                  <input type="color" value={form.color} onChange={e => set("color",e.target.value)} className="w-6 h-6 rounded-full cursor-pointer border-0"/>
                </div>
              </div>
            </div>
          </div>
          <div>
            <label className="block text-xs text-slate-500 mb-1 font-medium">Description</label>
            <input value={form.description} onChange={e => set("description",e.target.value)}
              placeholder="What does this agent specialise in?"
              className="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500"/>
          </div>
          <div>
            <label className="block text-xs text-slate-500 mb-2 font-medium">Live Data Sources</label>
            <div className="flex flex-wrap gap-2">
              {DS.map(ds => {
                const on = form.dataSources.includes(ds);
                return <button key={ds}
                  onClick={() => set("dataSources", on ? form.dataSources.filter(d=>d!==ds) : [...form.dataSources,ds])}
                  className={`px-3 py-1 rounded-full text-xs font-medium border transition ${on?"bg-blue-900/40 text-blue-300 border-blue-500":"border-slate-700 text-slate-400 hover:border-slate-500"}`}>{ds}</button>;
              })}
            </div>
          </div>
          <div>
            <label className="block text-xs text-slate-500 mb-1 font-medium">Report Style</label>
            <select value={form.reportStyle} onChange={e => set("reportStyle",e.target.value)}
              className="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
              <option value="detailed">Detailed — full row-by-row data</option>
              <option value="summary">Summary — totals and key metrics</option>
              <option value="executive">Executive — KPI cards + top-line table</option>
              <option value="comparison">Comparison — side-by-side analysis</option>
            </select>
          </div>
          <div>
            <label className="block text-xs text-slate-500 mb-1 font-medium">Custom AI Instructions</label>
            <textarea value={form.systemPromptExtra} onChange={e => set("systemPromptExtra",e.target.value)}
              placeholder="Special focus, calculations, column order, what to highlight..."
              rows={4} className="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 resize-none font-mono text-xs"/>
          </div>
          <div>
            <div className="flex items-center justify-between mb-2">
              <label className="text-xs text-slate-500 font-medium">Quick Prompts</label>
              <button onClick={() => setForm(f => ({...f,quickPrompts:[...f.quickPrompts,""]}))}
                className="text-xs text-blue-400 hover:text-blue-300 flex items-center gap-1">
                <Icon name="plus" size={11}/> Add
              </button>
            </div>
            <div className="space-y-2">
              {form.quickPrompts.map((p,i) => (
                <div key={i} className="flex gap-2 items-center">
                  <input value={p} onChange={e => setP(i,e.target.value)} placeholder={`Quick prompt ${i+1}`}
                    className="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500"/>
                  <button onClick={() => setForm(f => ({...f,quickPrompts:f.quickPrompts.filter((_,j)=>j!==i)}))}
                    className="p-1.5 text-slate-600 hover:text-red-400"><Icon name="x" size={12}/></button>
                </div>
              ))}
            </div>
          </div>
        </div>
        <div className="sticky bottom-0 flex justify-between items-center px-6 py-4 border-t border-slate-800 bg-slate-900 rounded-b-2xl">
          <button onClick={onCancel} className="px-4 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-800">Cancel</button>
          <button onClick={() => onSave(form)} disabled={!form.name.trim()}
            className="px-6 py-2 rounded-xl text-sm font-semibold text-white transition disabled:opacity-30 hover:opacity-90"
            style={{background:form.color}}>{agent ? "Save Changes" : "Create Agent"}</button>
        </div>
      </div>
    </div>
  );
}

// ─── Chat Panel ───────────────────────────────────────────────────────
function AgentChat({ agent, liveData, dataLoading, dataError, onReload }) {
  const [messages, setMessages] = useState([]);
  const [input, setInput]       = useState("");
  const [loading, setLoading]   = useState(false);
  const [report, setReport]     = useState(null);
  const [preview, setPreview]   = useState(false);
  const chatEnd = useRef(null);
  const inputRef = useRef(null);

  useEffect(() => { setMessages([]); setReport(null); setPreview(false); }, [agent.id]);
  useEffect(() => { chatEnd.current?.scrollIntoView({ behavior:"smooth" }); }, [messages]);

  const send = async (text) => {
    const msg = (text || input).trim();
    if (!msg || loading) return;
    setInput("");
    const next = [...messages, { role:"user", content:msg }];
    setMessages(next);
    setLoading(true);
    try {
      const html = await callAI(agent, next, liveData);
      const ts = new Date().toLocaleTimeString([],{hour:"2-digit",minute:"2-digit"});
      setMessages(p => [...p, { role:"assistant", content:html, ts }]);
      setReport(html); setPreview(true);
    } catch(err) {
      setMessages(p => [...p, {
        role:"assistant", isError:true, ts:"",
        content:`<div class="p-6 text-center"><p class="text-red-500 font-semibold text-sm">⚠️ ${err.message}</p><p class="text-xs text-gray-400 mt-2">Check: OPENROUTER_API_KEY in .env · routes registered · php artisan config:clear</p></div>`,
      }]);
    } finally { setLoading(false); setTimeout(() => inputRef.current?.focus(), 100); }
  };

  const doPrint = () => {
    if (!report) return;
    const w = window.open("","_blank");
    w.document.write(`<!DOCTYPE html><html><head><title>${agent.name} — FDE</title>
      <script src="https://cdn.tailwindcss.com"><\/script>
      <style>@media print{body{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}}body{font-family:system-ui,sans-serif}</style>
    </head><body class="p-8 bg-white">${report}
    <script>window.onload=()=>setTimeout(()=>window.print(),600)<\/script></body></html>`);
    w.document.close();
  };

  return (
    <div className="flex flex-col h-full">
      {/* Agent header */}
      <div className="shrink-0 flex items-center justify-between px-5 py-3 border-b border-slate-800" style={{background:rgba(agent.color,0.08)}}>
        <div className="flex items-center gap-3 min-w-0">
          <div className="w-9 h-9 rounded-xl flex items-center justify-center text-xl shrink-0" style={{background:rgba(agent.color,0.25)}}>{agent.icon}</div>
          <div className="min-w-0">
            <p className="text-sm font-bold text-white">{agent.name}</p>
            <DataPill loading={dataLoading} error={dataError} data={liveData} onReload={onReload}/>
          </div>
        </div>
        <div className="flex items-center gap-2 shrink-0">
          {report && <>
            <button onClick={() => setPreview(v => !v)}
              className="flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-lg bg-slate-800 border border-slate-700 hover:bg-slate-700 text-slate-300 transition">
              <Icon name="eye" size={12}/>{preview ? "Hide" : "Preview"}
            </button>
            <button onClick={doPrint}
              className="flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-lg text-white transition hover:opacity-85"
              style={{background:agent.color}}>
              <Icon name="print" size={12}/> Print
            </button>
            <button onClick={() => navigator.clipboard.writeText(report).then(() => alert("HTML copied"))}
              className="flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-lg bg-slate-800 border border-slate-700 hover:bg-slate-700 text-slate-300 transition">
              <Icon name="copy" size={12}/> HTML
            </button>
          </>}
        </div>
      </div>

      {/* Body */}
      <div className="flex flex-1 overflow-hidden">
        {/* Chat column */}
        <div className={`flex flex-col transition-all duration-300 ${preview && report ? "w-5/12" : "w-full"} border-r border-slate-800`}>
          <div className="flex-1 overflow-y-auto">
            {/* Empty state */}
            {messages.length === 0 && (
              <div className="p-5">
                <div className="text-center py-5 mb-4">
                  <div className="w-14 h-14 rounded-2xl mx-auto flex items-center justify-center text-3xl mb-3" style={{background:rgba(agent.color,0.2)}}>{agent.icon}</div>
                  <p className="text-sm font-bold text-white">{agent.name}</p>
                  <p className="text-xs text-slate-500 mt-1.5 max-w-xs mx-auto leading-relaxed">{agent.description}</p>
                  {liveData && (
                    <div className="mt-3 grid grid-cols-3 gap-2 max-w-xs mx-auto">
                      {[
                        { l:"Schools",   v: liveData.schools?.length ?? 0 },
                        { l:"Admitted",  v: (liveData.summary?.total_admitted ?? 0).toLocaleString() },
                        { l:"Fill Rate", v: (liveData.summary?.fill_rate ?? 0)+"%" },
                      ].map(s => (
                        <div key={s.l} className="bg-slate-800 rounded-xl p-2 text-center">
                          <p className="text-base font-bold" style={{color:agent.color}}>{s.v}</p>
                          <p className="text-[9px] text-slate-500 mt-0.5">{s.l}</p>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
                {agent.quickPrompts?.filter(Boolean).length > 0 && (
                  <div className="space-y-2">
                    <p className="text-[9px] text-slate-600 uppercase tracking-widest px-1 mb-2">Quick Prompts</p>
                    {agent.quickPrompts.filter(Boolean).map((p,i) => (
                      <button key={i} onClick={() => send(p)} disabled={dataLoading}
                        className="w-full text-left px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700 text-xs text-slate-300 hover:border-slate-500 hover:text-white hover:bg-slate-800 transition disabled:opacity-40 disabled:cursor-wait"
                        style={{borderLeftColor:agent.color, borderLeftWidth:3}}>
                        <span className="mr-2 opacity-50">→</span>{p}
                      </button>
                    ))}
                  </div>
                )}
              </div>
            )}
            {/* Messages */}
            {messages.length > 0 && (
              <div className="p-4 space-y-3">
                {messages.map((m,i) => (
                  <div key={i} className={`flex ${m.role==="user" ? "justify-end" : "justify-start"}`}>
                    {m.role === "user" ? (
                      <div className="max-w-[88%] rounded-2xl rounded-tr-sm px-4 py-2.5 text-sm text-white shadow-sm" style={{background:agent.color}}>{m.content}</div>
                    ) : (
                      <div className={`max-w-[90%] rounded-2xl rounded-tl-sm px-4 py-3 border ${m.isError ? "bg-red-950/40 border-red-800/50" : "bg-slate-800/80 border-slate-700"}`}>
                        {!m.isError && (
                          <div className="flex items-center justify-between gap-3 mb-2">
                            <span className="text-xs font-semibold" style={{color:agent.color}}>✓ Report ready</span>
                            <div className="flex items-center gap-2">
                              <span className="text-[10px] text-slate-600">{m.ts}</span>
                              <button onClick={() => { setReport(m.content); setPreview(true); }}
                                className="text-[10px] text-slate-400 hover:text-white flex items-center gap-1 transition px-2 py-0.5 rounded bg-slate-700 hover:bg-slate-600">
                                <Icon name="eye" size={10}/> View
                              </button>
                            </div>
                          </div>
                        )}
                        <div className={`text-[10px] rounded-lg px-3 py-2 ${m.isError?"text-red-300":"text-slate-500 bg-slate-900/60 border border-slate-700/50"}`}
                          style={{maxHeight:48,overflow:"hidden"}}>
                          {m.content.replace(/<[^>]+>/g," ").replace(/\s+/g," ").trim().slice(0,180)}…
                        </div>
                      </div>
                    )}
                  </div>
                ))}
                {loading && (
                  <div className="flex items-center gap-2.5 px-4 py-3 rounded-2xl rounded-tl-sm bg-slate-800/80 border border-slate-700 w-fit">
                    {[0,150,300].map(d => (
                      <div key={d} className="w-2 h-2 rounded-full animate-bounce" style={{background:agent.color, animationDelay:`${d}ms`}}/>
                    ))}
                    <span className="text-[10px] text-slate-500 ml-0.5">Generating report…</span>
                  </div>
                )}
                <div ref={chatEnd}/>
              </div>
            )}
          </div>
          {/* Input */}
          <div className="shrink-0 p-4 border-t border-slate-800">
            <div className="flex gap-2.5 items-end">
              <textarea ref={inputRef} value={input} onChange={e => setInput(e.target.value)}
                onKeyDown={e => { if (e.key==="Enter" && !e.shiftKey) { e.preventDefault(); send(); }}}
                placeholder={dataLoading ? "Fetching live data…" : `Ask ${agent.name} anything… (Enter to send)`}
                disabled={dataLoading} rows={2}
                className="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 placeholder-slate-600 resize-none focus:outline-none transition disabled:opacity-50"
                onFocus={e => e.target.style.borderColor=agent.color}
                onBlur={e => e.target.style.borderColor=""}/>
              <button onClick={() => send()} disabled={loading || dataLoading || !input.trim()}
                className="px-4 py-3 rounded-xl text-white font-bold transition disabled:opacity-30 shrink-0 hover:opacity-85"
                style={{background:agent.color}}>
                <Icon name="send" size={16}/>
              </button>
            </div>
            <p className="text-[9px] text-slate-700 mt-1.5 px-1">Shift+Enter for new line · reports use live DB data · API key stays server-side</p>
          </div>
        </div>

        {/* Preview */}
        {preview && report && (
          <div className="flex-1 bg-white overflow-y-auto">
            <div className="sticky top-0 flex items-center justify-between px-4 py-2 bg-gray-50 border-b border-gray-200 z-10">
              <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">Preview</span>
              <div className="flex gap-2">
                <button onClick={doPrint} className="flex items-center gap-1 px-3 py-1 text-xs rounded-lg bg-white border border-gray-300 hover:bg-gray-50 text-gray-600">
                  <Icon name="print" size={11}/> Print / PDF
                </button>
                <button onClick={() => setPreview(false)} className="p-1 rounded-lg hover:bg-gray-100 text-gray-400"><Icon name="x" size={14}/></button>
              </div>
            </div>
            <div className="p-6" dangerouslySetInnerHTML={{ __html: report }}/>
          </div>
        )}
      </div>
    </div>
  );
}

// ─── Main App ─────────────────────────────────────────────────────────
function AgentStudio() {
  const [agents, setAgents]       = useState(DEFAULT_AGENTS);
  const [activeId, setActiveId]   = useState(DEFAULT_AGENTS[0].id);
  const [building, setBuilding]   = useState(false);
  const [editAgent, setEditAgent] = useState(null);
  const [sideOpen, setSideOpen]   = useState(true);
  const { data, loading, error, reload } = useLiveData();

  const active = agents.find(a => a.id === activeId) || agents[0];

  const saveAgent = (form) => {
    setAgents(prev => {
      const exists = prev.find(a => a.id === form.id);
      return exists ? prev.map(a => a.id===form.id ? form : a) : [...prev, form];
    });
    setActiveId(form.id); setBuilding(false); setEditAgent(null);
  };
  const deleteAgent = (id) => {
    if (agents.length <= 1) return;
    const rem = agents.filter(a => a.id !== id);
    setAgents(rem);
    if (activeId === id) setActiveId(rem[0]?.id);
  };
  const duplicateAgent = (agent) => {
    const dup = { ...agent, id: Date.now().toString(), name: agent.name+" (copy)" };
    setAgents(prev => [...prev, dup]); setActiveId(dup.id);
  };

  return (
    <div className="h-full flex bg-slate-950 text-white overflow-hidden" style={{fontFamily:"system-ui,-apple-system,sans-serif"}}>

      {/* Sidebar */}
      {sideOpen && (
        <div className="w-64 shrink-0 flex flex-col bg-slate-900 border-r border-slate-800">
          <div className="px-4 py-3.5 border-b border-slate-800 flex items-center justify-between">
            <div className="flex items-center gap-2.5">
              <div className="w-7 h-7 rounded-lg bg-green-700 flex items-center justify-center text-[11px] font-black text-white">FDE</div>
              <div>
                <p className="text-xs font-bold text-white leading-none">Report Studio</p>
                <p className="text-[9px] text-slate-500 mt-0.5">{CTX.academicYear} · {CTX.user.name}</p>
              </div>
            </div>
            <button onClick={() => setSideOpen(false)} className="p-1 rounded hover:bg-slate-800 text-slate-500"><Icon name="x" size={14}/></button>
          </div>

          <div className="flex-1 overflow-y-auto py-2 px-2">
            <p className="text-[9px] text-slate-600 uppercase tracking-widest px-2 pb-1.5 mt-1">Agents</p>
            {agents.map(a => (
              <AgentCard key={a.id} agent={a} active={activeId===a.id}
                onClick={() => setActiveId(a.id)}
                onEdit={() => { setEditAgent(a); setBuilding(true); }}
                onDelete={() => deleteAgent(a.id)}
                onDuplicate={() => duplicateAgent(a)}/>
            ))}
          </div>

          <div className="p-3 border-t border-slate-800">
            <button onClick={() => { setEditAgent(null); setBuilding(true); }}
              className="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-green-700 hover:bg-green-600 transition">
              <Icon name="plus" size={13}/> New Agent
            </button>
          </div>
        </div>
      )}

      {/* Main */}
      <div className="flex-1 flex flex-col overflow-hidden">
        {!sideOpen && (
          <div className="shrink-0 flex items-center gap-3 px-4 py-3 border-b border-slate-800 bg-slate-900">
            <button onClick={() => setSideOpen(true)} className="p-1.5 rounded-lg hover:bg-slate-800 text-slate-400"><Icon name="menu" size={18}/></button>
            <span className="text-lg">{active.icon}</span>
            <span className="text-sm font-semibold">{active.name}</span>
            <div className="ml-auto flex gap-2">
              {agents.map(a => (
                <button key={a.id} onClick={() => setActiveId(a.id)} title={a.name}
                  className="w-7 h-7 rounded-lg flex items-center justify-center text-sm transition"
                  style={{background:activeId===a.id?rgba(a.color,0.3):rgba(a.color,0.1), border:`1px solid ${activeId===a.id?a.color:"transparent"}`}}>
                  {a.icon}
                </button>
              ))}
              <button onClick={() => { setEditAgent(null); setBuilding(true); }}
                className="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700">
                <Icon name="plus" size={14}/>
              </button>
            </div>
          </div>
        )}
        <div className="flex-1 overflow-hidden">
          <AgentChat key={active.id} agent={active}
            liveData={data} dataLoading={loading} dataError={error} onReload={reload}/>
        </div>
      </div>

      {building && (
        <AgentBuilder agent={editAgent} onSave={saveAgent}
          onCancel={() => { setBuilding(false); setEditAgent(null); }}/>
      )}
    </div>
  );
}

// Mount
const _root = document.getElementById("root");
if (_root) ReactDOM.createRoot(_root).render(React.createElement(AgentStudio));
