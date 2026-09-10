const pptxgen = require("pptxgenjs");

const pres = new pptxgen();
pres.layout = "LAYOUT_16x9";
pres.title = "TrustMeRecycle Pitch Deck";
pres.author = "TrustMeRecycle";

// Palette
const C = {
  forestDark:  "1B5E20",
  forest:      "2E7D32",
  mid:         "388E3C",
  lightGreen:  "A5D6A7",
  paleGreen:   "E8F5E9",
  white:       "FFFFFF",
  offWhite:    "F9FAF9",
  textDark:    "1C2B1E",
  textMid:     "4A6B4E",
  textLight:   "757575",
  accent:      "00C853",
  gold:        "FFC107",
  cardBorder:  "C8E6C9",
};

const FONT_H = "Calibri";
const FONT_B = "Calibri";

// ─── Helper: shadow ──────────────────────────────────────────────────────────
const mkShadow = () => ({ type: "outer", blur: 8, offset: 3, angle: 135, color: "000000", opacity: 0.10 });

// ─── SLIDE 1: COVER ──────────────────────────────────────────────────────────
{
  const s = pres.addSlide();
  s.background = { color: C.forestDark };

  // Left panel accent
  s.addShape(pres.shapes.RECTANGLE, {
    x: 0, y: 0, w: 0.35, h: 5.625,
    fill: { color: C.accent },
    line: { color: C.accent },
  });

  // Recycling symbol (text emoji used as large glyph)
  s.addText("♻", {
    x: 0.6, y: 0.45, w: 1.8, h: 1.8,
    fontSize: 80, align: "center", valign: "middle",
    color: C.lightGreen,
  });

  // Brand name
  s.addText("TrustMeRecycle", {
    x: 0.55, y: 2.1, w: 5.8, h: 1.1,
    fontSize: 52, fontFace: FONT_H, bold: true,
    color: C.white, align: "left", margin: 0,
  });

  // Tagline
  s.addText("Scrap Collection Made Easy — End-to-End Recycling Logistics for India", {
    x: 0.55, y: 3.15, w: 5.6, h: 0.65,
    fontSize: 17, fontFace: FONT_B,
    color: C.lightGreen, align: "left", margin: 0, italic: true,
  });

  // Domain
  s.addText("trustmerecycle.in", {
    x: 0.55, y: 4.1, w: 3, h: 0.4,
    fontSize: 13, fontFace: FONT_B,
    color: C.accent, align: "left", margin: 0,
  });

  // Right visual block – big recycle stat
  s.addShape(pres.shapes.RECTANGLE, {
    x: 6.9, y: 0.5, w: 2.7, h: 4.6,
    fill: { color: C.forest },
    line: { color: C.forest },
    shadow: mkShadow(),
  });

  const circleItems = [
    { emoji: "🏭", label: "14+ Sites" },
    { emoji: "📱", label: "Mobile App" },
    { emoji: "♻️", label: "B2B2B SaaS" },
    { emoji: "📊", label: "Realtime Data" },
  ];
  circleItems.forEach((item, i) => {
    const cy = 0.75 + i * 1.05;
    s.addText(item.emoji, {
      x: 7.0, y: cy, w: 1.0, h: 0.6,
      fontSize: 22, align: "center", valign: "middle",
    });
    s.addText(item.label, {
      x: 8.0, y: cy + 0.05, w: 1.5, h: 0.5,
      fontSize: 13, fontFace: FONT_B, bold: true,
      color: C.white, align: "left", valign: "middle", margin: 0,
    });
  });

  // Date
  s.addText("June 2026", {
    x: 0.55, y: 4.8, w: 3, h: 0.35,
    fontSize: 11, fontFace: FONT_B,
    color: C.textMid, align: "left", margin: 0,
  });
}

// ─── SLIDE 2: THE PROBLEM ─────────────────────────────────────────────────────
{
  const s = pres.addSlide();
  s.background = { color: C.offWhite };

  // Title
  s.addText("The Problem", {
    x: 0.5, y: 0.3, w: 9, h: 0.65,
    fontSize: 36, fontFace: FONT_H, bold: true,
    color: C.forestDark, align: "left", margin: 0,
  });
  s.addShape(pres.shapes.RECTANGLE, {
    x: 0.5, y: 0.97, w: 1.2, h: 0.05,
    fill: { color: C.accent }, line: { color: C.accent },
  });

  const problems = [
    { icon: "🗂️", title: "Fragmented Operations", body: "Recycling logistics run on spreadsheets, WhatsApp messages, and phone calls — no central visibility." },
    { icon: "📦", title: "No Inventory Tracking", body: "Godowns overflow silently. Businesses have no live view of stock levels across their collection network." },
    { icon: "🚛", title: "Uncoordinated Dispatch", body: "Trucks are sent without proof-of-pickup, route data, or weight verification, causing revenue leakage." },
    { icon: "📉", title: "Zero Analytics", body: "No historical data on scrap volumes, material types, or vendor performance — decisions made blind." },
  ];

  problems.forEach((p, i) => {
    const col = i % 2;
    const row = Math.floor(i / 2);
    const x = 0.5 + col * 4.8;
    const y = 1.25 + row * 2.05;

    s.addShape(pres.shapes.RECTANGLE, {
      x, y, w: 4.4, h: 1.8,
      fill: { color: C.white },
      line: { color: C.cardBorder, pt: 1.5 },
      shadow: mkShadow(),
    });
    s.addText(p.icon, {
      x: x + 0.15, y: y + 0.15, w: 0.6, h: 0.5,
      fontSize: 22, align: "center",
    });
    s.addText(p.title, {
      x: x + 0.8, y: y + 0.15, w: 3.45, h: 0.45,
      fontSize: 14, fontFace: FONT_H, bold: true,
      color: C.forestDark, align: "left", margin: 0,
    });
    s.addText(p.body, {
      x: x + 0.15, y: y + 0.65, w: 4.1, h: 1.0,
      fontSize: 12, fontFace: FONT_B,
      color: C.textMid, align: "left", valign: "top", margin: 0,
    });
  });
}

// ─── SLIDE 3: THE SOLUTION ─────────────────────────────────────────────────────
{
  const s = pres.addSlide();
  s.background = { color: C.forestDark };

  s.addText("Our Solution", {
    x: 0.5, y: 0.3, w: 9, h: 0.65,
    fontSize: 36, fontFace: FONT_H, bold: true,
    color: C.white, align: "left", margin: 0,
  });
  s.addText("One platform. Three roles. Complete recycling lifecycle.", {
    x: 0.5, y: 0.95, w: 9, h: 0.45,
    fontSize: 16, fontFace: FONT_B,
    color: C.lightGreen, align: "left", margin: 0, italic: true,
  });

  const steps = [
    { num: "01", title: "Request", body: "Customer submits scrap pickup via mobile app with address, material type & estimated weight", color: C.accent },
    { num: "02", title: "Assign", body: "Admin reviews request, assigns nearest available godown & creates a collection job in one click", color: "66BB6A" },
    { num: "03", title: "Collect", body: "Vendor dispatches truck with driver details, completes pickup with photo proof & weight entry", color: "A5D6A7" },
    { num: "04", title: "Verify", body: "System syncs status, updates inventory, triggers notifications and generates audit-ready reports", color: C.lightGreen },
  ];

  steps.forEach((step, i) => {
    const x = 0.4 + i * 2.35;
    s.addShape(pres.shapes.RECTANGLE, {
      x, y: 1.65, w: 2.1, h: 3.4,
      fill: { color: C.forest },
      line: { color: C.mid, pt: 1 },
    });
    // Number badge
    s.addShape(pres.shapes.OVAL, {
      x: x + 0.65, y: 1.8, w: 0.8, h: 0.8,
      fill: { color: step.color },
      line: { color: step.color },
    });
    s.addText(step.num, {
      x: x + 0.65, y: 1.8, w: 0.8, h: 0.8,
      fontSize: 16, fontFace: FONT_H, bold: true,
      color: C.forestDark, align: "center", valign: "middle", margin: 0,
    });
    s.addText(step.title, {
      x: x + 0.1, y: 2.75, w: 1.9, h: 0.5,
      fontSize: 18, fontFace: FONT_H, bold: true,
      color: C.white, align: "center", margin: 0,
    });
    s.addText(step.body, {
      x: x + 0.1, y: 3.3, w: 1.9, h: 1.6,
      fontSize: 11, fontFace: FONT_B,
      color: C.lightGreen, align: "center", valign: "top", margin: 0,
    });
    // Arrow between steps
    if (i < 3) {
      s.addShape(pres.shapes.RECTANGLE, {
        x: x + 2.12, y: 2.2, w: 0.2, h: 0.05,
        fill: { color: C.accent }, line: { color: C.accent },
      });
    }
  });
}

// ─── SLIDE 4: MARKET OPPORTUNITY ─────────────────────────────────────────────
{
  const s = pres.addSlide();
  s.background = { color: C.offWhite };

  s.addText("Market Opportunity", {
    x: 0.5, y: 0.3, w: 9, h: 0.65,
    fontSize: 36, fontFace: FONT_H, bold: true,
    color: C.forestDark, align: "left", margin: 0,
  });

  // Big stats row
  const stats = [
    { val: "$14B+", label: "India Recycling Market\n(2024)", sub: "Growing at 7.2% CAGR" },
    { val: "90%", label: "Informal Sector\nOperations", sub: "No digital tracking today" },
    { val: "4.5M+", label: "Tonnes of Plastic\nWaste Annually", sub: "Regulatory pressure rising" },
  ];

  stats.forEach((st, i) => {
    const x = 0.5 + i * 3.15;
    s.addShape(pres.shapes.RECTANGLE, {
      x, y: 1.2, w: 2.85, h: 2.4,
      fill: { color: C.forest },
      line: { color: C.forest },
      shadow: mkShadow(),
    });
    s.addText(st.val, {
      x: x + 0.1, y: 1.35, w: 2.65, h: 1.0,
      fontSize: 48, fontFace: FONT_H, bold: true,
      color: C.accent, align: "center", margin: 0,
    });
    s.addText(st.label, {
      x: x + 0.1, y: 2.35, w: 2.65, h: 0.6,
      fontSize: 12, fontFace: FONT_B, bold: true,
      color: C.white, align: "center", margin: 0,
    });
    s.addText(st.sub, {
      x: x + 0.1, y: 2.95, w: 2.65, h: 0.5,
      fontSize: 10, fontFace: FONT_B,
      color: C.lightGreen, align: "center", margin: 0, italic: true,
    });
  });

  // Context paragraph
  s.addText(
    "India's waste management sector is undergoing rapid digitisation driven by EPR (Extended Producer Responsibility) mandates, " +
    "GST traceability requirements, and ESG commitments from large enterprises. The informal kabadiwala network lacks accountability, " +
    "traceability, or data — creating a massive whitespace for a structured, tech-enabled platform.",
    {
      x: 0.5, y: 3.85, w: 9, h: 1.4,
      fontSize: 13, fontFace: FONT_B,
      color: C.textMid, align: "left", valign: "top", margin: 0,
    }
  );
}

// ─── SLIDE 5: HOW IT WORKS ────────────────────────────────────────────────────
{
  const s = pres.addSlide();
  s.background = { color: C.white };

  s.addText("How It Works", {
    x: 0.5, y: 0.3, w: 9, h: 0.65,
    fontSize: 36, fontFace: FONT_H, bold: true,
    color: C.forestDark, align: "left", margin: 0,
  });

  // Three actor columns
  const actors = [
    {
      role: "CUSTOMER",
      color: C.forest,
      steps: ["Downloads mobile app", "Registers with name & phone", "Submits pickup request\n(address, scrap type,\nestimated weight)", "Tracks status in real-time", "Views collection history"],
    },
    {
      role: "ADMIN",
      color: C.forestDark,
      steps: ["Reviews incoming requests", "Checks godown capacity\n& proximity", "Assigns vendor + godown\n(1-click auto job creation)", "Dispatches truck with\ndriver & vehicle details", "Exports collection reports"],
    },
    {
      role: "VENDOR",
      color: "1B5E20",
      steps: ["Receives notification\non mobile app", "Views job details:\ngodown, truck, address", "Travels to customer site", "Captures photo proof\n& weighs collected scrap", "Marks job complete"],
    },
  ];

  actors.forEach((a, i) => {
    const x = 0.4 + i * 3.15;
    // Header
    s.addShape(pres.shapes.RECTANGLE, {
      x, y: 1.1, w: 2.85, h: 0.55,
      fill: { color: a.color },
      line: { color: a.color },
    });
    s.addText(a.role, {
      x, y: 1.1, w: 2.85, h: 0.55,
      fontSize: 14, fontFace: FONT_H, bold: true,
      color: C.white, align: "center", valign: "middle", margin: 0,
    });
    // Steps
    a.steps.forEach((step, j) => {
      const sy = 1.78 + j * 0.75;
      s.addShape(pres.shapes.OVAL, {
        x: x + 0.12, y: sy + 0.04, w: 0.3, h: 0.3,
        fill: { color: a.color },
        line: { color: a.color },
      });
      s.addText(String(j + 1), {
        x: x + 0.12, y: sy + 0.04, w: 0.3, h: 0.3,
        fontSize: 9, fontFace: FONT_H, bold: true,
        color: C.white, align: "center", valign: "middle", margin: 0,
      });
      s.addText(step, {
        x: x + 0.5, y: sy, w: 2.2, h: 0.65,
        fontSize: 11, fontFace: FONT_B,
        color: C.textDark, align: "left", valign: "middle", margin: 0,
      });
    });
  });
}

// ─── SLIDE 6: PLATFORM OVERVIEW ───────────────────────────────────────────────
{
  const s = pres.addSlide();
  s.background = { color: C.forestDark };

  s.addText("Platform Overview", {
    x: 0.5, y: 0.3, w: 9, h: 0.65,
    fontSize: 36, fontFace: FONT_H, bold: true,
    color: C.white, align: "left", margin: 0,
  });
  s.addText("Three interfaces. One unified data layer.", {
    x: 0.5, y: 0.93, w: 9, h: 0.4,
    fontSize: 15, fontFace: FONT_B,
    color: C.lightGreen, align: "left", margin: 0, italic: true,
  });

  const panels = [
    {
      icon: "🖥️",
      title: "Admin Web Panel",
      subtitle: "/admin — Full control",
      features: ["Godown & vendor management", "Truck dispatch workflow", "Pickup request assignment", "Scrap type & pricing CRUD", "Collection reports (Excel/PDF)", "Capacity alerts & analytics"],
      color: C.forest,
    },
    {
      icon: "📱",
      title: "Vendor Mobile App",
      subtitle: "iOS & Android — Field ops",
      features: ["Assigned job dashboard", "State → City → Site picker", "Photo proof capture", "Weight entry & submission", "Offline support + sync", "Collection history & export"],
      color: C.mid,
    },
    {
      icon: "👤",
      title: "Customer Mobile App",
      subtitle: "iOS & Android — Self-serve",
      features: ["Self-registration flow", "Pickup request creation", "Real-time status tracking", "Collection history view", "Assigned vendor details", "Preferred date scheduling"],
      color: "2E7D32",
    },
  ];

  panels.forEach((p, i) => {
    const x = 0.4 + i * 3.15;
    s.addShape(pres.shapes.RECTANGLE, {
      x, y: 1.5, w: 2.85, h: 3.7,
      fill: { color: p.color },
      line: { color: C.mid, pt: 1 },
      shadow: mkShadow(),
    });
    s.addText(p.icon, {
      x, y: 1.6, w: 2.85, h: 0.6,
      fontSize: 28, align: "center",
    });
    s.addText(p.title, {
      x: x + 0.1, y: 2.22, w: 2.65, h: 0.45,
      fontSize: 15, fontFace: FONT_H, bold: true,
      color: C.white, align: "center", margin: 0,
    });
    s.addText(p.subtitle, {
      x: x + 0.1, y: 2.67, w: 2.65, h: 0.35,
      fontSize: 10, fontFace: FONT_B,
      color: C.accent, align: "center", margin: 0, italic: true,
    });
    p.features.forEach((f, j) => {
      s.addText([
        { text: "✓  ", options: { color: C.accent, bold: true } },
        { text: f, options: { color: C.lightGreen } },
      ], {
        x: x + 0.15, y: 3.1 + j * 0.34, w: 2.55, h: 0.32,
        fontSize: 10.5, fontFace: FONT_B, align: "left", margin: 0,
      });
    });
  });
}

// ─── SLIDE 7: ADMIN DASHBOARD ────────────────────────────────────────────────
{
  const s = pres.addSlide();
  s.background = { color: C.offWhite };

  s.addText("Admin Dashboard", {
    x: 0.5, y: 0.3, w: 9, h: 0.65,
    fontSize: 36, fontFace: FONT_H, bold: true,
    color: C.forestDark, align: "left", margin: 0,
  });
  s.addText("Filament 3 web panel — full operational command center", {
    x: 0.5, y: 0.93, w: 9, h: 0.35,
    fontSize: 14, fontFace: FONT_B,
    color: C.textMid, align: "left", margin: 0, italic: true,
  });

  const features = [
    { icon: "🏭", title: "Godown Management", body: "Live capacity tracking with color-coded alerts: green (<80%), yellow (80-99%), red (100%+). View stock levels across all sites." },
    { icon: "🚛", title: "Truck Dispatch", body: "One-click dispatch with driver name, vehicle number and notes. Creates CollectionJob and notifies vendor instantly." },
    { icon: "📋", title: "Pickup Requests", body: "Review customer requests, match to available godowns, auto-create collection jobs with a single assign action." },
    { icon: "💰", title: "Scrap Pricing", body: "Manage material types with per-ton pricing: Plastic ₹5k, Metal ₹15k, Electronics ₹25k, Paper ₹3k, Glass ₹2k." },
    { icon: "📊", title: "Reports & Export", body: "Per-godown collection history exportable as HTML, Excel (.xlsx) or PDF. Full audit trail with timestamps." },
    { icon: "👥", title: "Vendor Management", body: "Create site incharge accounts, assign multiple godowns, track performance and manage access permissions." },
  ];

  features.forEach((f, i) => {
    const col = i % 3;
    const row = Math.floor(i / 3);
    const x = 0.4 + col * 3.15;
    const y = 1.45 + row * 2.0;

    s.addShape(pres.shapes.RECTANGLE, {
      x, y, w: 2.85, h: 1.75,
      fill: { color: C.white },
      line: { color: C.cardBorder, pt: 1 },
      shadow: mkShadow(),
    });
    s.addShape(pres.shapes.RECTANGLE, {
      x, y, w: 0.06, h: 1.75,
      fill: { color: C.forest },
      line: { color: C.forest },
    });
    s.addText(f.icon, {
      x: x + 0.15, y: y + 0.15, w: 0.55, h: 0.55,
      fontSize: 24, align: "center",
    });
    s.addText(f.title, {
      x: x + 0.75, y: y + 0.15, w: 2.0, h: 0.45,
      fontSize: 13, fontFace: FONT_H, bold: true,
      color: C.forestDark, align: "left", margin: 0,
    });
    s.addText(f.body, {
      x: x + 0.15, y: y + 0.72, w: 2.6, h: 0.9,
      fontSize: 10.5, fontFace: FONT_B,
      color: C.textMid, align: "left", valign: "top", margin: 0,
    });
  });
}

// ─── SLIDE 8: VENDOR MOBILE APP ───────────────────────────────────────────────
{
  const s = pres.addSlide();
  s.background = { color: C.white };

  s.addText("Vendor Mobile App", {
    x: 0.5, y: 0.3, w: 9, h: 0.65,
    fontSize: 36, fontFace: FONT_H, bold: true,
    color: C.forestDark, align: "left", margin: 0,
  });
  s.addText("Field collection tool built for on-the-ground scrap vendors", {
    x: 0.5, y: 0.93, w: 9, h: 0.35,
    fontSize: 14, fontFace: FONT_B,
    color: C.textMid, align: "left", margin: 0, italic: true,
  });

  // Left: feature list
  const features = [
    { icon: "📋", title: "Job Dashboard", body: "View all assigned collection jobs with status, godown address, and truck details at a glance." },
    { icon: "📍", title: "Location Picker", body: "Drill-down from state → city → warehouse. Supports all 28 Indian states with dynamic filtering." },
    { icon: "📸", title: "Photo Proof", body: "Capture collection evidence via camera or gallery. Image required before marking job complete." },
    { icon: "⚖️", title: "Weight Recording", body: "Enter collected weight in MT. System auto-updates godown inventory and syncs pickup request status." },
    { icon: "📶", title: "Offline Support", body: "Works without internet — shows cached jobs with clear offline banner. Syncs when connection restored." },
    { icon: "🔒", title: "Secure Auth", body: "Encrypted token storage, 15-second timeout handling, automatic session expiry on 401 responses." },
  ];

  features.forEach((f, i) => {
    const y = 1.45 + i * 0.69;
    s.addShape(pres.shapes.OVAL, {
      x: 0.4, y: y + 0.08, w: 0.45, h: 0.45,
      fill: { color: C.paleGreen },
      line: { color: C.lightGreen },
    });
    s.addText(f.icon, {
      x: 0.4, y: y + 0.08, w: 0.45, h: 0.45,
      fontSize: 16, align: "center", valign: "middle",
    });
    s.addText(f.title + " — ", {
      x: 0.95, y: y + 0.07, w: 1.5, h: 0.5,
      fontSize: 12, fontFace: FONT_H, bold: true,
      color: C.forestDark, align: "left", margin: 0,
    });
    s.addText(f.body, {
      x: 2.4, y: y + 0.07, w: 4.05, h: 0.5,
      fontSize: 11.5, fontFace: FONT_B,
      color: C.textMid, align: "left", valign: "middle", margin: 0,
    });
  });

  // Right: stat callouts
  const callouts = [
    { val: "4", label: "Job Statuses\nTracked" },
    { val: "28", label: "Indian States\nSupported" },
    { val: "15s", label: "API Timeout\nSafety Net" },
  ];
  callouts.forEach((c, i) => {
    const cy = 1.5 + i * 1.38;
    s.addShape(pres.shapes.RECTANGLE, {
      x: 6.75, y: cy, w: 2.75, h: 1.15,
      fill: { color: C.forest },
      line: { color: C.forest },
      shadow: mkShadow(),
    });
    s.addText(c.val, {
      x: 6.75, y: cy + 0.1, w: 2.75, h: 0.6,
      fontSize: 42, fontFace: FONT_H, bold: true,
      color: C.accent, align: "center", margin: 0,
    });
    s.addText(c.label, {
      x: 6.75, y: cy + 0.7, w: 2.75, h: 0.38,
      fontSize: 11, fontFace: FONT_B,
      color: C.lightGreen, align: "center", margin: 0,
    });
  });
}

// ─── SLIDE 9: CUSTOMER APP ────────────────────────────────────────────────────
{
  const s = pres.addSlide();
  s.background = { color: C.offWhite };

  s.addText("Customer Mobile App", {
    x: 0.5, y: 0.3, w: 9, h: 0.65,
    fontSize: 36, fontFace: FONT_H, bold: true,
    color: C.forestDark, align: "left", margin: 0,
  });
  s.addText("Self-serve scrap pickup — from request to completion in minutes", {
    x: 0.5, y: 0.93, w: 9, h: 0.35,
    fontSize: 14, fontFace: FONT_B,
    color: C.textMid, align: "left", margin: 0, italic: true,
  });

  // Status flow
  const statuses = [
    { label: "Pending\nReview", color: "EF9A9A" },
    { label: "Assigned", color: C.gold },
    { label: "Truck\nDispatched", color: "90CAF9" },
    { label: "Completed", color: "A5D6A7" },
    { label: "Cancelled", color: "BDBDBD" },
  ];

  s.addText("Request Lifecycle:", {
    x: 0.5, y: 1.45, w: 3, h: 0.4,
    fontSize: 13, fontFace: FONT_H, bold: true,
    color: C.forestDark, align: "left", margin: 0,
  });

  statuses.forEach((st, i) => {
    const x = 0.4 + i * 1.85;
    s.addShape(pres.shapes.RECTANGLE, {
      x, y: 1.9, w: 1.65, h: 0.8,
      fill: { color: st.color },
      line: { color: st.color },
    });
    s.addText(st.label, {
      x, y: 1.9, w: 1.65, h: 0.8,
      fontSize: 11, fontFace: FONT_B, bold: true,
      color: C.textDark, align: "center", valign: "middle", margin: 0,
    });
    if (i < 3) {
      s.addText("→", {
        x: x + 1.65, y: 2.1, w: 0.2, h: 0.4,
        fontSize: 14, color: C.textLight, align: "center", margin: 0,
      });
    }
  });

  // Feature grid
  const cFeatures = [
    { icon: "📝", title: "Easy Registration", body: "Name, phone, email & password. No OTP friction. Instant access to pickup requests." },
    { icon: "📅", title: "Preferred Scheduling", body: "Set preferred pickup date. Specify material details, estimated weight, and location notes." },
    { icon: "📊", title: "Summary Dashboard", body: "See total, open, and completed requests. Last pickup date always visible at a glance." },
    { icon: "🔔", title: "Status Tracking", body: "Monitor active pickup in real time — from pending review through truck dispatched to completion." },
    { icon: "📜", title: "Full History", body: "Complete pickup history with collected weight, assigned vendor, and pickup timestamps." },
    { icon: "🌙", title: "Dark Mode", body: "System-aware dark/light theme with persistent preference. Comfortable for all lighting conditions." },
  ];

  cFeatures.forEach((f, i) => {
    const col = i % 3;
    const row = Math.floor(i / 3);
    const x = 0.4 + col * 3.15;
    const y = 2.9 + row * 1.35;

    s.addShape(pres.shapes.RECTANGLE, {
      x, y, w: 2.85, h: 1.2,
      fill: { color: C.white },
      line: { color: C.cardBorder, pt: 1 },
      shadow: mkShadow(),
    });
    s.addText(f.icon + "  " + f.title, {
      x: x + 0.15, y: y + 0.1, w: 2.55, h: 0.38,
      fontSize: 12, fontFace: FONT_H, bold: true,
      color: C.forestDark, align: "left", margin: 0,
    });
    s.addText(f.body, {
      x: x + 0.15, y: y + 0.5, w: 2.55, h: 0.62,
      fontSize: 10.5, fontFace: FONT_B,
      color: C.textMid, align: "left", valign: "top", margin: 0,
    });
  });
}

// ─── SLIDE 10: TECHNOLOGY STACK ───────────────────────────────────────────────
{
  const s = pres.addSlide();
  s.background = { color: C.forestDark };

  s.addText("Technology Stack", {
    x: 0.5, y: 0.3, w: 9, h: 0.65,
    fontSize: 36, fontFace: FONT_H, bold: true,
    color: C.white, align: "left", margin: 0,
  });
  s.addText("Production-grade, battle-tested open source stack", {
    x: 0.5, y: 0.93, w: 9, h: 0.35,
    fontSize: 14, fontFace: FONT_B,
    color: C.lightGreen, align: "left", margin: 0, italic: true,
  });

  const layers = [
    {
      layer: "Backend",
      items: [
        { name: "Laravel 11", role: "PHP framework — routing, ORM, auth, queues, notifications" },
        { name: "Filament 3", role: "Admin & vendor panel — CRUD, widgets, actions, role isolation" },
        { name: "Laravel Sanctum", role: "Token-based mobile API auth with per-ability permissions" },
        { name: "MySQL / MariaDB", role: "Relational store for all entities with cascade integrity" },
      ],
    },
    {
      layer: "Mobile",
      items: [
        { name: "React Native 0.84", role: "Cross-platform iOS + Android app from single codebase" },
        { name: "TypeScript", role: "Type-safe components, API models, context & hooks" },
        { name: "React Navigation", role: "Native stack navigator with role-based screen trees" },
        { name: "Encrypted Storage", role: "Secure JWT token persistence — not exposed to JS layer" },
      ],
    },
    {
      layer: "Infrastructure",
      items: [
        { name: "XAMPP / Apache", role: "Local dev environment; Hostinger for production hosting" },
        { name: "Maatwebsite Excel", role: "Server-side .xlsx and PDF report generation" },
        { name: "Observer Pattern", role: "Auto stock updates and notifications on scrap entry" },
        { name: "trustmerecycle.in", role: "Live production domain with API endpoints active" },
      ],
    },
  ];

  layers.forEach((layer, li) => {
    const x = 0.35 + li * 3.2;
    s.addShape(pres.shapes.RECTANGLE, {
      x, y: 1.45, w: 2.95, h: 0.45,
      fill: { color: C.accent },
      line: { color: C.accent },
    });
    s.addText(layer.layer, {
      x, y: 1.45, w: 2.95, h: 0.45,
      fontSize: 13, fontFace: FONT_H, bold: true,
      color: C.forestDark, align: "center", valign: "middle", margin: 0,
    });
    layer.items.forEach((item, ii) => {
      const iy = 2.05 + ii * 0.85;
      s.addShape(pres.shapes.RECTANGLE, {
        x, y: iy, w: 2.95, h: 0.75,
        fill: { color: C.forest },
        line: { color: C.mid, pt: 0.5 },
      });
      s.addText(item.name, {
        x: x + 0.12, y: iy + 0.05, w: 2.7, h: 0.3,
        fontSize: 12, fontFace: FONT_H, bold: true,
        color: C.accent, align: "left", margin: 0,
      });
      s.addText(item.role, {
        x: x + 0.12, y: iy + 0.34, w: 2.7, h: 0.36,
        fontSize: 10, fontFace: FONT_B,
        color: C.lightGreen, align: "left", margin: 0,
      });
    });
  });
}

// ─── SLIDE 11: BUSINESS MODEL ─────────────────────────────────────────────────
{
  const s = pres.addSlide();
  s.background = { color: C.white };

  s.addText("Business Model", {
    x: 0.5, y: 0.3, w: 9, h: 0.65,
    fontSize: 36, fontFace: FONT_H, bold: true,
    color: C.forestDark, align: "left", margin: 0,
  });
  s.addText("B2B2B SaaS — serving enterprises, logistics vendors, and scrap generators", {
    x: 0.5, y: 0.93, w: 9, h: 0.35,
    fontSize: 14, fontFace: FONT_B,
    color: C.textMid, align: "left", margin: 0, italic: true,
  });

  const streams = [
    { icon: "💼", title: "SaaS Subscription", body: "Monthly/annual license for enterprise recycling operations. Tiers based on number of sites and admin users.", badge: "Core Revenue" },
    { icon: "📦", title: "Per-Collection Fee", body: "Small transaction fee per verified pickup. Scales automatically with platform volume — no manual effort.", badge: "Usage-Based" },
    { icon: "🤝", title: "B2B Integration", body: "API access for large retailers (Blinkit, Zepto, Amazon, Flipkart). Branded pickup scheduling embedded in their ops.", badge: "Enterprise" },
    { icon: "📈", title: "Analytics & Reports", body: "Premium ESG reporting add-on. Exportable compliance data for EPR filings and sustainability disclosures.", badge: "Upsell" },
  ];

  streams.forEach((st, i) => {
    const x = 0.4 + (i % 2) * 4.8;
    const y = 1.45 + Math.floor(i / 2) * 2.0;
    s.addShape(pres.shapes.RECTANGLE, {
      x, y, w: 4.4, h: 1.75,
      fill: { color: C.offWhite },
      line: { color: C.cardBorder, pt: 1.5 },
      shadow: mkShadow(),
    });
    // badge
    s.addShape(pres.shapes.RECTANGLE, {
      x: x + 2.7, y: y + 0.15, w: 1.55, h: 0.3,
      fill: { color: C.forest },
      line: { color: C.forest },
    });
    s.addText(st.badge, {
      x: x + 2.7, y: y + 0.15, w: 1.55, h: 0.3,
      fontSize: 9, fontFace: FONT_H, bold: true,
      color: C.white, align: "center", valign: "middle", margin: 0,
    });
    s.addText(st.icon, {
      x: x + 0.15, y: y + 0.12, w: 0.5, h: 0.5,
      fontSize: 22, align: "center",
    });
    s.addText(st.title, {
      x: x + 0.7, y: y + 0.15, w: 1.9, h: 0.45,
      fontSize: 14, fontFace: FONT_H, bold: true,
      color: C.forestDark, align: "left", margin: 0,
    });
    s.addText(st.body, {
      x: x + 0.15, y: y + 0.7, w: 4.1, h: 0.95,
      fontSize: 12, fontFace: FONT_B,
      color: C.textMid, align: "left", valign: "top", margin: 0,
    });
  });
}

// ─── SLIDE 12: TRACTION ───────────────────────────────────────────────────────
{
  const s = pres.addSlide();
  s.background = { color: C.forestDark };

  s.addText("Traction & Validation", {
    x: 0.5, y: 0.3, w: 9, h: 0.65,
    fontSize: 36, fontFace: FONT_H, bold: true,
    color: C.white, align: "left", margin: 0,
  });
  s.addText("Live in production — real sites, real collections, real data", {
    x: 0.5, y: 0.93, w: 9, h: 0.35,
    fontSize: 14, fontFace: FONT_B,
    color: C.lightGreen, align: "left", margin: 0, italic: true,
  });

  const metrics = [
    { val: "14+", label: "Active Godown Sites", sub: "Lucknow, Delhi, Bengaluru" },
    { val: "3", label: "User Roles Deployed", sub: "Admin, Vendor, Customer" },
    { val: "5", label: "Scrap Material Types", sub: "With live pricing engine" },
    { val: "100%", label: "API Coverage", sub: "All flows production-tested" },
  ];

  metrics.forEach((m, i) => {
    const x = 0.35 + i * 2.35;
    s.addShape(pres.shapes.RECTANGLE, {
      x, y: 1.5, w: 2.1, h: 1.9,
      fill: { color: C.forest },
      line: { color: C.mid, pt: 1 },
      shadow: mkShadow(),
    });
    s.addText(m.val, {
      x, y: 1.6, w: 2.1, h: 0.85,
      fontSize: 44, fontFace: FONT_H, bold: true,
      color: C.accent, align: "center", margin: 0,
    });
    s.addText(m.label, {
      x, y: 2.48, w: 2.1, h: 0.5,
      fontSize: 11, fontFace: FONT_H, bold: true,
      color: C.white, align: "center", margin: 0,
    });
    s.addText(m.sub, {
      x, y: 2.98, w: 2.1, h: 0.35,
      fontSize: 9.5, fontFace: FONT_B,
      color: C.lightGreen, align: "center", margin: 0, italic: true,
    });
  });

  // Partner logos bar
  s.addText("Serving collections for:", {
    x: 0.5, y: 3.65, w: 9, h: 0.4,
    fontSize: 13, fontFace: FONT_H, bold: true,
    color: C.lightGreen, align: "left", margin: 0,
  });

  const brands = ["Blinkit", "Zepto", "Zomato", "Amazon", "Flipkart"];
  brands.forEach((b, i) => {
    const x = 0.4 + i * 1.88;
    s.addShape(pres.shapes.RECTANGLE, {
      x, y: 4.1, w: 1.65, h: 0.65,
      fill: { color: C.forest },
      line: { color: C.mid, pt: 1 },
    });
    s.addText(b, {
      x, y: 4.1, w: 1.65, h: 0.65,
      fontSize: 12, fontFace: FONT_H, bold: true,
      color: C.white, align: "center", valign: "middle", margin: 0,
    });
  });
}

// ─── SLIDE 13: ROADMAP ────────────────────────────────────────────────────────
{
  const s = pres.addSlide();
  s.background = { color: C.white };

  s.addText("Product Roadmap", {
    x: 0.5, y: 0.3, w: 9, h: 0.65,
    fontSize: 36, fontFace: FONT_H, bold: true,
    color: C.forestDark, align: "left", margin: 0,
  });

  const quarters = [
    {
      q: "NOW", label: "Live",
      color: C.forest,
      items: ["Admin + vendor web panels", "Customer mobile app (iOS/Android)", "14-site godown network", "Photo-proof collection flow", "Excel/PDF reporting"],
    },
    {
      q: "Q3 2026", label: "Scale",
      color: C.mid,
      items: ["Customer analytics dashboard", "Push notifications (FCM)", "Multi-language support (Hindi)", "Bulk godown import/export", "Vendor performance scoring"],
    },
    {
      q: "Q4 2026", label: "Intelligence",
      color: "388E3C",
      items: ["Route optimization engine", "Predictive capacity alerts", "QR-coded challan generation", "Customer invoicing module", "WhatsApp bot integration"],
    },
    {
      q: "2027", label: "Marketplace",
      color: C.accent,
      items: ["Open marketplace pricing", "Carbon credit tracking", "Government EPR reporting", "Third-party vendor onboarding", "Pan-India expansion"],
    },
  ];

  // Horizontal timeline line
  s.addShape(pres.shapes.RECTANGLE, {
    x: 0.5, y: 2.0, w: 9, h: 0.06,
    fill: { color: C.cardBorder },
    line: { color: C.cardBorder },
  });

  quarters.forEach((q, i) => {
    const x = 0.4 + i * 2.35;
    // Dot on timeline
    s.addShape(pres.shapes.OVAL, {
      x: x + 0.8, y: 1.82, w: 0.42, h: 0.42,
      fill: { color: q.color },
      line: { color: q.color },
    });
    // Quarter label
    s.addText(q.q, {
      x, y: 1.3, w: 2.1, h: 0.45,
      fontSize: 13, fontFace: FONT_H, bold: true,
      color: q.color, align: "center", margin: 0,
    });
    s.addText(q.label, {
      x, y: 1.72, w: 2.1, h: 0.3,
      fontSize: 10, fontFace: FONT_B,
      color: C.textLight, align: "center", margin: 0,
    });
    // Feature list below timeline
    s.addShape(pres.shapes.RECTANGLE, {
      x, y: 2.28, w: 2.1, h: 3.0,
      fill: { color: C.offWhite },
      line: { color: C.cardBorder, pt: 1 },
    });
    s.addShape(pres.shapes.RECTANGLE, {
      x, y: 2.28, w: 2.1, h: 0.08,
      fill: { color: q.color },
      line: { color: q.color },
    });
    q.items.forEach((item, j) => {
      s.addText([
        { text: "•  ", options: { color: q.color, bold: true } },
        { text: item, options: { color: C.textDark } },
      ], {
        x: x + 0.12, y: 2.43 + j * 0.52, w: 1.85, h: 0.46,
        fontSize: 10.5, fontFace: FONT_B, align: "left", margin: 0,
      });
    });
  });
}

// ─── SLIDE 14: CONTACT / CTA ──────────────────────────────────────────────────
{
  const s = pres.addSlide();
  s.background = { color: C.forestDark };

  // Bottom accent strip
  s.addShape(pres.shapes.RECTANGLE, {
    x: 0, y: 5.1, w: 10, h: 0.525,
    fill: { color: C.forest },
    line: { color: C.forest },
  });
  s.addText("Building the backbone of India's circular economy", {
    x: 0.5, y: 5.12, w: 9, h: 0.45,
    fontSize: 13, fontFace: FONT_B,
    color: C.lightGreen, align: "center", margin: 0, italic: true,
  });

  // Recycling symbol large
  s.addText("♻", {
    x: 3.8, y: 0.2, w: 2.4, h: 2.0,
    fontSize: 90, align: "center", valign: "middle",
    color: C.mid,
  });

  s.addText("Let's Build a Cleaner India", {
    x: 0.5, y: 2.1, w: 9, h: 0.85,
    fontSize: 38, fontFace: FONT_H, bold: true,
    color: C.white, align: "center", margin: 0,
  });
  s.addText("Together.", {
    x: 0.5, y: 2.85, w: 9, h: 0.65,
    fontSize: 38, fontFace: FONT_H, bold: true,
    color: C.accent, align: "center", margin: 0,
  });

  s.addText("🌐  trustmerecycle.in", {
    x: 2.0, y: 3.75, w: 2.8, h: 0.48,
    fontSize: 15, fontFace: FONT_B,
    color: C.lightGreen, align: "center", margin: 0,
  });
  s.addText("📧  admin@trustmerecycle.com", {
    x: 5.2, y: 3.75, w: 3.5, h: 0.48,
    fontSize: 15, fontFace: FONT_B,
    color: C.lightGreen, align: "center", margin: 0,
  });

  s.addText("© 2026 TrustMeRecycle · Scrap Collection Made Easy", {
    x: 0.5, y: 4.55, w: 9, h: 0.4,
    fontSize: 11, fontFace: FONT_B,
    color: C.textMid, align: "center", margin: 0,
  });
}

// ─── WRITE FILE ───────────────────────────────────────────────────────────────
pres.writeFile({ fileName: "/Applications/XAMPP/xamppfiles/htdocs/trustmerecycle/TrustMeRecycle_Pitch_Deck.pptx" })
  .then(() => console.log("✅ Pitch deck written: TrustMeRecycle_Pitch_Deck.pptx"))
  .catch(err => { console.error("❌ Error:", err); process.exit(1); });
