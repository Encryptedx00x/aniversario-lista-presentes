/* EDITORIAL DINÂMICO — kinetic typography + paper folding + ink masks + cursor trail */

const ITEMS = [
  { id: "I", w: "Fones",      sub: "Sennheiser HD 660S2",     n: "n.º 01", tag: "ÁUDIO",     color: "#1A1A1F", accent: "#7A1F2B" },
  { id: "II", w: "Tênis",     sub: "Salomon XT-6",             n: "n.º 02", tag: "PISADA",   color: "#2E2A24", accent: "#5C4A2C" },
  { id: "III", w: "Whisky",   sub: "Yamazaki 12 anos",         n: "n.º 03", tag: "BAR",      color: "#5C2E1F", accent: "#8B3A1E" },
  { id: "IV", w: "Câmera",    sub: "Ricoh GR IIIx",            n: "n.º 04", tag: "ÓPTICA",   color: "#1A1814", accent: "#3F1F22" },
  { id: "V",  w: "Cadeira",   sub: "Herman Miller Aeron",      n: "n.º 05", tag: "OFÍCIO",   color: "#2A2823", accent: "#4A2E36" },
  { id: "VI", w: "Relógio",   sub: "Moonswatch Mars",          n: "n.º 06", tag: "TEMPO",    color: "#3F1A1A", accent: "#7A1F2B" },
];

// Hover-words: when hovered, an image appears tracking the cursor
const HOVER_WORDS = {
  silêncio:    { color: "#1A1A1F", accent: "#7A1F2B", glyph: "○" },
  pisada:      { color: "#2E2A24", accent: "#5C4A2C", glyph: "↗" },
  domingos:    { color: "#5C2E1F", accent: "#8B3A1E", glyph: "✦" },
  luz:         { color: "#1A1814", accent: "#3F1F22", glyph: "◐" },
  mãos:        { color: "#2A2823", accent: "#4A2E36", glyph: "✿" },
  tempo:       { color: "#3F1A1A", accent: "#7A1F2B", glyph: "✱" },
  obrigado:    { color: "#1A1A1F", accent: "#7A1F2B", glyph: "★" },
};

function App() {
  const [scroll, setScroll] = React.useState(0);
  const [vh, setVh] = React.useState(typeof window !== "undefined" ? window.innerHeight : 800);
  const [mouse, setMouse] = React.useState({ x: 0, y: 0 });
  const [smoothMouse, setSmoothMouse] = React.useState({ x: 0, y: 0 });
  const [hoverWord, setHoverWord] = React.useState(null);
  const [pageTransition, setPageTransition] = React.useState(null); // "in" | "out" | null
  const [route, setRoute] = React.useState("home");

  // Scroll listener with rAF smoothing
  React.useEffect(() => {
    let raf = null;
    const onScroll = () => {
      if (raf) return;
      raf = requestAnimationFrame(() => {
        setScroll(window.scrollY);
        raf = null;
      });
    };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    const onResize = () => setVh(window.innerHeight);
    window.addEventListener("resize", onResize);
    return () => { window.removeEventListener("scroll", onScroll); window.removeEventListener("resize", onResize); };
  }, []);

  // Mouse with eased follow
  React.useEffect(() => {
    const onMove = (e) => setMouse({ x: e.clientX, y: e.clientY });
    window.addEventListener("mousemove", onMove);
    return () => window.removeEventListener("mousemove", onMove);
  }, []);

  React.useEffect(() => {
    let raf;
    const tick = () => {
      setSmoothMouse((m) => ({
        x: m.x + (mouse.x - m.x) * 0.12,
        y: m.y + (mouse.y - m.y) * 0.12,
      }));
      raf = requestAnimationFrame(tick);
    };
    raf = requestAnimationFrame(tick);
    return () => cancelAnimationFrame(raf);
  }, [mouse]);

  const navigate = (r) => {
    if (r === route) return;
    setPageTransition("out");
    setTimeout(() => { setRoute(r); window.scrollTo(0, 0); setPageTransition("in"); }, 700);
    setTimeout(() => { setPageTransition(null); }, 1500);
  };

  return (
    <div style={{ background: "#EDE6D8", color: "#1A1A1F", minHeight: "100vh", overflowX: "hidden" }}>
      {/* Macro-video background driven by scroll */}
      <BackgroundFilm scroll={scroll} />

      {/* Paper grain overlay */}
      <PaperGrain />

      {/* Custom cursor */}
      <Cursor x={smoothMouse.x} y={smoothMouse.y} hoverWord={hoverWord} />

      {/* Hover-tracking image */}
      {hoverWord && <CursorTrail x={smoothMouse.x} y={smoothMouse.y} word={hoverWord} />}

      {/* Top masthead */}
      <Masthead route={route} navigate={navigate} />

      <main style={{ position: "relative", zIndex: 2 }}>
        {route === "home" && <Home scroll={scroll} vh={vh} setHoverWord={setHoverWord} mouse={smoothMouse} navigate={navigate} />}
        {route === "atelier" && <Atelier scroll={scroll} vh={vh} setHoverWord={setHoverWord} navigate={navigate} />}
        {route === "ritual" && <Ritual scroll={scroll} vh={vh} setHoverWord={setHoverWord} />}
      </main>

      {/* Page transition: ink mask reveal */}
      <InkMask phase={pageTransition} />

      <GlobalStyles />
    </div>
  );
}

/* ===== BACKGROUND FILM (scroll-driven smoke) ===== */
function BackgroundFilm({ scroll }) {
  // Generate a procedural smoke field on canvas, frame index = scroll/8 mod N
  const ref = React.useRef(null);
  const FRAMES = 80;
  const frame = Math.floor((scroll / 6) % FRAMES);

  React.useEffect(() => {
    const canvas = ref.current;
    if (!canvas) return;
    const w = canvas.width = 800;
    const h = canvas.height = 800;
    const ctx = canvas.getContext("2d");

    // Procedural ink-in-water field for current frame
    const t = frame / FRAMES;
    ctx.clearRect(0, 0, w, h);

    // Base creme wash
    const g = ctx.createRadialGradient(w / 2, h / 2, 50, w / 2, h / 2, w * 0.7);
    g.addColorStop(0, "rgba(237, 230, 216, 0)");
    g.addColorStop(1, "rgba(237, 230, 216, 0.0)");
    ctx.fillStyle = g;
    ctx.fillRect(0, 0, w, h);

    // Several blobs of ink that drift with t
    const blobs = [
      { c: "rgba(122, 31, 43, 0.34)",  cx: 0.3, cy: 0.3, drift: { x: 0.4, y: 0.6 }, r: 0.55, freq: 0.7 },
      { c: "rgba(80, 20, 30, 0.28)",   cx: 0.7, cy: 0.6, drift: { x: -0.3, y: 0.4 }, r: 0.48, freq: 1.1 },
      { c: "rgba(40, 30, 25, 0.30)",   cx: 0.5, cy: 0.85, drift: { x: 0.5, y: -0.5 }, r: 0.6, freq: 0.5 },
      { c: "rgba(110, 40, 60, 0.22)",  cx: 0.2, cy: 0.7, drift: { x: 0.6, y: -0.3 }, r: 0.5, freq: 0.9 },
    ];

    for (const b of blobs) {
      const cx = (b.cx + Math.sin(t * Math.PI * 2 * b.freq) * 0.08 + b.drift.x * t * 0.15) * w;
      const cy = (b.cy + Math.cos(t * Math.PI * 2 * b.freq * 0.7) * 0.06 + b.drift.y * t * 0.15) * h;
      const r = (b.r + Math.sin(t * Math.PI * 2) * 0.06) * w;
      const grad = ctx.createRadialGradient(cx, cy, 5, cx, cy, r);
      grad.addColorStop(0, b.c);
      grad.addColorStop(0.5, b.c.replace(/[\d.]+\)$/, "0.12)"));
      grad.addColorStop(1, "rgba(0,0,0,0)");
      ctx.fillStyle = grad;
      ctx.fillRect(0, 0, w, h);
    }

    // Subtle filaments — ink wisps
    ctx.globalCompositeOperation = "multiply";
    for (let i = 0; i < 24; i++) {
      const seed = i * 7.31 + t * 6.28;
      const x = (Math.sin(seed) * 0.5 + 0.5) * w;
      const y = (Math.cos(seed * 0.8) * 0.5 + 0.5) * h;
      const len = 80 + Math.sin(seed * 2) * 60;
      const ang = seed + t * Math.PI;
      ctx.strokeStyle = `rgba(60, 18, 24, ${0.04 + (Math.sin(seed * 3) * 0.5 + 0.5) * 0.08})`;
      ctx.lineWidth = 1 + Math.sin(seed * 5);
      ctx.beginPath();
      ctx.moveTo(x, y);
      ctx.bezierCurveTo(
        x + Math.cos(ang) * len * 0.4, y + Math.sin(ang) * len * 0.4,
        x + Math.cos(ang + 1) * len * 0.7, y + Math.sin(ang + 1) * len * 0.7,
        x + Math.cos(ang) * len, y + Math.sin(ang) * len
      );
      ctx.stroke();
    }
    ctx.globalCompositeOperation = "source-over";
  }, [frame]);

  return (
    <div aria-hidden style={{
      position: "fixed", inset: 0, zIndex: 0, pointerEvents: "none",
      filter: "blur(2px) saturate(1.1)",
    }}>
      <canvas ref={ref} style={{ width: "100%", height: "100%", opacity: 0.85 }} />
    </div>
  );
}

/* ===== PAPER GRAIN ===== */
function PaperGrain() {
  return (
    <>
      <div aria-hidden style={{
        position: "fixed", inset: 0, zIndex: 1, pointerEvents: "none",
        backgroundImage: "url(\"data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='240' height='240'><filter id='n'><feTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='2' stitchTiles='stitch'/><feColorMatrix values='0 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 0.18 0'/></filter><rect width='100%' height='100%' filter='url(%23n)'/></svg>\")",
        opacity: 0.55, mixBlendMode: "multiply",
      }}></div>
      <div aria-hidden style={{
        position: "fixed", inset: 0, zIndex: 1, pointerEvents: "none",
        background: "radial-gradient(ellipse at center, transparent 40%, rgba(60, 30, 20, 0.18) 100%)",
      }}></div>
    </>
  );
}

/* ===== CUSTOM CURSOR ===== */
function Cursor({ x, y, hoverWord }) {
  const big = !!hoverWord;
  return (
    <>
      <div aria-hidden style={{
        position: "fixed", left: x, top: y, zIndex: 9999, pointerEvents: "none",
        width: big ? 80 : 14, height: big ? 80 : 14, borderRadius: "50%",
        border: "1px solid #1A1A1F",
        transform: "translate(-50%, -50%)",
        transition: "width 360ms cubic-bezier(0.2,0.8,0.2,1), height 360ms cubic-bezier(0.2,0.8,0.2,1), border-color 240ms",
        mixBlendMode: "multiply",
      }}></div>
      <div aria-hidden style={{
        position: "fixed", left: x, top: y, zIndex: 9999, pointerEvents: "none",
        width: 4, height: 4, borderRadius: "50%", background: "#1A1A1F",
        transform: "translate(-50%, -50%)", opacity: big ? 0 : 1, transition: "opacity 200ms",
      }}></div>
    </>
  );
}

/* ===== HOVER-TRACKING IMAGE TRAIL ===== */
function CursorTrail({ x, y, word }) {
  // Lagging copies of an artistic block, building a trail
  const trails = 4;
  return (
    <>
      {Array.from({ length: trails }).map((_, i) => {
        const offset = i * 26;
        const rot = (i - 1) * 6;
        const scale = 1 - i * 0.12;
        const opacity = 1 - i * 0.22;
        return (
          <div key={i} aria-hidden style={{
            position: "fixed", left: x, top: y, zIndex: 60, pointerEvents: "none",
            width: 220, height: 280,
            transform: `translate(${-110 + offset * 0.3}px, ${-140 + offset * 0.2}px) rotate(${rot}deg) scale(${scale})`,
            transition: `transform ${260 + i * 80}ms cubic-bezier(0.2,0.8,0.2,1), opacity 240ms`,
            opacity,
            background: word.color,
            boxShadow: `0 30px 80px -20px ${word.accent}80`,
            overflow: "hidden",
          }}>
            <div style={{
              position: "absolute", inset: 0,
              backgroundImage: `radial-gradient(circle at 30% 30%, ${word.accent}60, transparent 70%), radial-gradient(circle at 70% 80%, rgba(255,255,255,0.08), transparent 70%)`,
              filter: "blur(8px)",
            }}></div>
            <div style={{
              position: "absolute", inset: 0,
              backgroundImage: "url(\"data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='200' height='200'><filter id='n'><feTurbulence type='fractalNoise' baseFrequency='1.4' numOctaves='2'/></filter><rect width='100%' height='100%' filter='url(%23n)' opacity='0.4'/></svg>\")",
              mixBlendMode: "overlay", opacity: 0.5,
            }}></div>
            <div style={{
              position: "absolute", left: 18, top: 18, color: "rgba(237, 230, 216, 0.5)",
              fontFamily: "'Cormorant Garamond', serif", fontSize: 14, fontStyle: "italic", letterSpacing: "0.18em", textTransform: "uppercase",
            }}>{word.glyph}</div>
            <div style={{
              position: "absolute", right: 18, bottom: 18, color: "rgba(237, 230, 216, 0.95)",
              fontFamily: "'Cormorant Garamond', serif", fontSize: 32, fontStyle: "italic", lineHeight: 1, textAlign: "right",
            }}>—<br/>{word.glyph}</div>
            <div style={{
              position: "absolute", left: 18, bottom: 18, color: "rgba(237, 230, 216, 0.4)",
              fontFamily: "'Inter', sans-serif", fontSize: 10, letterSpacing: "0.3em", textTransform: "uppercase",
            }}>plate {String(i + 1).padStart(2, "0")}</div>
          </div>
        );
      }).reverse()}
    </>
  );
}

/* ===== MASTHEAD ===== */
function Masthead({ route, navigate }) {
  return (
    <header style={{
      position: "fixed", top: 0, left: 0, right: 0, zIndex: 50,
      padding: "20px 40px",
      display: "grid", gridTemplateColumns: "1fr auto 1fr", alignItems: "baseline",
      mixBlendMode: "multiply",
    }}>
      <div style={{ fontFamily: "'Cormorant Garamond', serif", fontSize: 11, letterSpacing: "0.42em", textTransform: "uppercase", color: "#1A1A1F" }}>
        Folha N° XXX · Junho · MMXXVI
      </div>
      <div style={{ fontFamily: "'Cormorant Garamond', serif", fontSize: 16, fontStyle: "italic", letterSpacing: "0.08em", color: "#1A1A1F" }}>
        Trinta — uma efeméride
      </div>
      <nav style={{ display: "flex", gap: 28, justifyContent: "flex-end", fontFamily: "'Cormorant Garamond', serif", fontSize: 12, letterSpacing: "0.32em", textTransform: "uppercase" }}>
        {[["home", "Vestíbulo"], ["atelier", "Ateliê"], ["ritual", "Ritual"]].map(([k, l]) => (
          <button key={k} onClick={() => navigate(k)} style={{
            background: "transparent", border: "none", padding: 0, cursor: "pointer",
            fontFamily: "inherit", fontSize: "inherit", letterSpacing: "inherit", textTransform: "inherit",
            color: "#1A1A1F", borderBottom: route === k ? "1px solid #7A1F2B" : "1px solid transparent",
            paddingBottom: 4, transition: "border-color 240ms",
          }}>{l}</button>
        ))}
      </nav>
    </header>
  );
}

/* ===== HOME ===== */
function Home({ scroll, vh, setHoverWord, mouse, navigate }) {
  return (
    <>
      {/* ACT I — Folio cover with kinetic title */}
      <section style={{ minHeight: "120vh", padding: "140px 40px 80px", position: "relative" }}>
        <FolioMeta />
        <KineticTitle scroll={scroll} mouse={mouse} />

        <div style={{
          position: "absolute", left: "50%", top: "50%", transform: "translate(-50%, -50%)",
          fontFamily: "'Cormorant Garamond', serif", fontStyle: "italic", fontSize: 20,
          color: "#7A1F2B", whiteSpace: "nowrap", pointerEvents: "none", zIndex: 4,
          opacity: Math.max(0, 1 - scroll / 400),
        }}>
          ↓  &nbsp; role para abrir &nbsp; ↓
        </div>

        <ScrollHint scroll={scroll} />
      </section>

      {/* ACT II — Manifesto with hover-words */}
      <section style={{ padding: "180px 40px 220px", position: "relative", maxWidth: 1280, margin: "0 auto" }}>
        <FoldedReveal scroll={scroll} threshold={vh * 0.9} delay={0}>
          <div style={{ fontFamily: "'Cormorant Garamond', serif", fontSize: 12, letterSpacing: "0.42em", textTransform: "uppercase", color: "#7A1F2B", marginBottom: 60 }}>
            ¶ Manifesto
          </div>
        </FoldedReveal>

        <p style={{
          fontFamily: "'Cormorant Garamond', serif", fontWeight: 400,
          fontSize: 56, lineHeight: 1.18, letterSpacing: "-0.012em", color: "#1A1A1F",
          maxWidth: 1100, margin: 0,
        }}>
          <FoldedReveal scroll={scroll} threshold={vh * 0.95} delay={0}>
            Aos trinta, descobri o valor do
            {" "}<HoverWord word="silêncio" setHoverWord={setHoverWord}>silêncio</HoverWord>{" "}
            entre as coisas. De um par de
          </FoldedReveal>{" "}
          <FoldedReveal scroll={scroll} threshold={vh * 1.05} delay={120}>
            <HoverWord word="pisada" setHoverWord={setHoverWord}>tênis</HoverWord>{" "}
            que aguenta a cidade e a trilha. De
          </FoldedReveal>{" "}
          <FoldedReveal scroll={scroll} threshold={vh * 1.15} delay={240}>
            <HoverWord word="domingos" setHoverWord={setHoverWord}>domingos</HoverWord>{" "}
            sem pressa,
          </FoldedReveal>{" "}
          <FoldedReveal scroll={scroll} threshold={vh * 1.25} delay={360}>
            de uma <HoverWord word="luz" setHoverWord={setHoverWord}>câmera</HoverWord>{" "}
            no bolso, e do <HoverWord word="tempo" setHoverWord={setHoverWord}>tempo</HoverWord>{" "}
            entregue às <HoverWord word="mãos" setHoverWord={setHoverWord}>mãos</HoverWord> certas.
          </FoldedReveal>
        </p>

        <div style={{ marginTop: 100, display: "grid", gridTemplateColumns: "1fr 1fr", gap: 48 }}>
          <FoldedReveal scroll={scroll} threshold={vh * 1.4} delay={0}>
            <div style={{ fontFamily: "'Inter', sans-serif", fontSize: 14, color: "#3a2a26", lineHeight: 1.7, maxWidth: 460 }}>
              Esta página não é uma loja. É uma folha de papel onde anotei, com paciência, oito objetos que tenho considerado faz tempo. Você pode reservar um, ou simplesmente passar — e, se quiser, deixar um recado ao final.
            </div>
          </FoldedReveal>
          <FoldedReveal scroll={scroll} threshold={vh * 1.5} delay={120}>
            <div style={{ display: "flex", flexDirection: "column", alignItems: "flex-start", gap: 18 }}>
              <button onClick={() => navigate("atelier")} className="ed-btn" style={editorialBtn}>
                Entrar no Ateliê
                <span className="ed-arrow" style={{ display: "inline-block", marginLeft: 18, transition: "transform 320ms cubic-bezier(0.2,0.8,0.2,1)" }}>—→</span>
              </button>
              <div style={{ fontFamily: "'Cormorant Garamond', serif", fontStyle: "italic", fontSize: 14, color: "#7A1F2B", letterSpacing: "0.04em" }}>
                ou diretamente, ao <button onClick={() => navigate("ritual")} style={{ background: "transparent", border: "none", padding: 0, fontFamily: "inherit", fontStyle: "inherit", fontSize: "inherit", color: "inherit", cursor: "pointer", borderBottom: "1px solid #7A1F2B" }}>ritual</button>.
              </div>
            </div>
          </FoldedReveal>
        </div>
      </section>

      {/* ACT III — Plates / collage */}
      <section style={{ padding: "60px 40px 240px", position: "relative" }}>
        <Plates scroll={scroll} vh={vh} navigate={navigate} />
      </section>

      {/* ACT IV — Closing */}
      <section style={{ padding: "120px 40px 200px", textAlign: "center", position: "relative" }}>
        <KineticClosing scroll={scroll} setHoverWord={setHoverWord} />
      </section>
    </>
  );
}

/* Folio meta — corner labels */
function FolioMeta() {
  return (
    <>
      <div style={{ position: "absolute", left: 40, top: 100, fontFamily: "'Cormorant Garamond', serif", fontStyle: "italic", fontSize: 12, letterSpacing: "0.12em", color: "#3a2a26" }}>
        Caderno I — Vestíbulo
      </div>
      <div style={{ position: "absolute", right: 40, top: 100, fontFamily: "'Cormorant Garamond', serif", fontSize: 12, letterSpacing: "0.32em", textTransform: "uppercase", color: "#3a2a26" }}>
        XII · VI · MMXXVI
      </div>
    </>
  );
}

/* Kinetic Title — letters fold/stretch with scroll */
function KineticTitle({ scroll, mouse }) {
  const t = Math.min(1, scroll / 600);
  const word1 = "TRINTA";
  const word2 = "anos";

  return (
    <div style={{ position: "relative", marginTop: 60, perspective: 1400 }}>
      {/* Tag line above */}
      <div style={{
        textAlign: "center", marginBottom: 40,
        fontFamily: "'Cormorant Garamond', serif", fontStyle: "italic",
        fontSize: 22, letterSpacing: "0.04em", color: "#7A1F2B",
        opacity: 1 - t * 1.5, transform: `translateY(${t * -40}px)`,
      }}>
        — uma página dobrada em —
      </div>

      <h1 style={{
        margin: 0, textAlign: "center",
        fontFamily: "'Cormorant Garamond', serif", fontWeight: 700,
        fontSize: "clamp(140px, 22vw, 320px)", lineHeight: 0.82,
        letterSpacing: "-0.04em", color: "#1A1A1F",
      }}>
        <span style={{ display: "block", whiteSpace: "nowrap" }}>
          {word1.split("").map((ch, i) => {
            const stagger = i * 0.06;
            const local = Math.max(0, Math.min(1, (t - stagger) / 0.6));
            const fold = local * 80;
            const stretch = 1 + local * 0.18;
            const ty = local * 60;
            const skew = local * (i % 2 ? 4 : -4);
            return (
              <span key={i} style={{
                display: "inline-block", transformOrigin: "center bottom",
                transform: `translateY(${ty}px) rotateX(${fold}deg) skewX(${skew}deg) scaleY(${stretch})`,
                transition: "transform 80ms linear",
                marginRight: i === word1.length - 1 ? 0 : "0.02em",
                color: i === 2 ? "#7A1F2B" : "#1A1A1F",
              }}>{ch}</span>
            );
          })}
        </span>
        <span style={{
          display: "block", marginTop: -20,
          fontStyle: "italic", fontWeight: 400,
          fontSize: "clamp(80px, 14vw, 200px)",
          color: "#7A1F2B",
          letterSpacing: "-0.02em",
          transform: `translate(${(mouse.x - window.innerWidth / 2) * 0.02}px, ${t * 80}px)`,
          transition: "transform 100ms linear",
        }}>
          {word2.split("").map((ch, i) => {
            const stagger = 0.3 + i * 0.05;
            const local = Math.max(0, Math.min(1, (t - stagger) / 0.6));
            return (
              <span key={i} style={{
                display: "inline-block",
                transform: `translateY(${local * -40}px) rotate(${local * (i % 2 ? 8 : -8)}deg)`,
                transition: "transform 80ms linear",
              }}>{ch}</span>
            );
          })}
        </span>
        <span style={{
          display: "block", marginTop: -10,
          fontFamily: "'Cormorant Garamond', serif", fontWeight: 700,
          fontSize: "clamp(140px, 22vw, 320px)",
          color: "#1A1A1F",
          letterSpacing: "-0.04em",
        }}>
          {"de boas escolhas".split("").map((ch, i) => {
            const stagger = 0.5 + i * 0.025;
            const local = Math.max(0, Math.min(1, (t - stagger) / 0.5));
            const fold = local * -60;
            return (
              <span key={i} style={{
                display: "inline-block", transformOrigin: "center top",
                transform: `translateY(${local * 30}px) rotateX(${fold}deg)`,
                transition: "transform 80ms linear",
                color: ch === "b" || ch === "e" ? "#7A1F2B" : undefined,
                marginRight: ch === " " ? "0.18em" : "0.01em",
                whiteSpace: "pre",
              }}>{ch}</span>
            );
          })}
        </span>
      </h1>
    </div>
  );
}

function ScrollHint({ scroll }) {
  const op = Math.max(0, 1 - scroll / 200);
  return (
    <div style={{
      position: "absolute", left: 40, bottom: 40,
      display: "flex", alignItems: "center", gap: 14,
      fontFamily: "'Inter', sans-serif", fontSize: 11, letterSpacing: "0.32em", textTransform: "uppercase",
      color: "#3a2a26", opacity: op,
    }}>
      <div style={{ width: 60, height: 1, background: "#3a2a26", position: "relative", overflow: "hidden" }}>
        <div style={{ position: "absolute", left: 0, top: 0, width: 20, height: "100%", background: "#7A1F2B", animation: "slideRight 2.4s ease-in-out infinite" }}></div>
      </div>
      role para descobrir
    </div>
  );
}

/* Hover word: registers + reveals image trail */
function HoverWord({ word, setHoverWord, children }) {
  const config = HOVER_WORDS[word];
  return (
    <span
      onMouseEnter={() => setHoverWord(config)}
      onMouseLeave={() => setHoverWord(null)}
      style={{
        fontStyle: "italic", color: "#7A1F2B", cursor: "none",
        borderBottom: "1px dotted #7A1F2B", paddingBottom: 2,
        transition: "color 200ms",
      }}>{children}</span>
  );
}

/* Folded reveal — content unfolds like paper as it enters viewport */
function FoldedReveal({ children, scroll, threshold, delay = 0 }) {
  const ref = React.useRef(null);
  const [progress, setProgress] = React.useState(0);

  React.useEffect(() => {
    if (!ref.current) return;
    const r = ref.current.getBoundingClientRect();
    const top = r.top + window.scrollY;
    const enter = top - window.innerHeight * 0.85;
    const p = Math.max(0, Math.min(1, (scroll - enter) / 200));
    setProgress(p);
  }, [scroll]);

  return (
    <span ref={ref} style={{
      display: "inline-block",
      transform: `translateY(${(1 - progress) * 24}px) rotateX(${(1 - progress) * 70}deg)`,
      transformOrigin: "center top",
      opacity: progress,
      transition: `transform 700ms ${delay}ms cubic-bezier(0.2,0.8,0.2,1), opacity 500ms ${delay}ms`,
    }}>{children}</span>
  );
}

/* PLATES — collage grid of items */
function Plates({ scroll, vh, navigate }) {
  return (
    <div style={{ position: "relative", maxWidth: 1280, margin: "0 auto" }}>
      <div style={{ fontFamily: "'Cormorant Garamond', serif", fontSize: 12, letterSpacing: "0.42em", textTransform: "uppercase", color: "#7A1F2B", marginBottom: 24 }}>
        ¶ Pranchas
      </div>
      <div style={{ fontFamily: "'Cormorant Garamond', serif", fontSize: 100, lineHeight: 0.95, letterSpacing: "-0.03em", margin: 0, color: "#1A1A1F", marginBottom: 80 }}>
        Oito coisas,<br/>em <em style={{ color: "#7A1F2B", fontWeight: 700 }}>seis pranchas.</em>
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "repeat(12, 1fr)", gap: 32, alignItems: "start" }}>
        {ITEMS.map((it, i) => {
          // Asymmetric layout — alternating spans + offsets
          const layouts = [
            { col: "1 / span 5", row: 1, h: 520, ty: 0,    rot: -2 },
            { col: "7 / span 4", row: 1, h: 380, ty: 80,   rot: 1.5 },
            { col: "3 / span 5", row: 2, h: 460, ty: -40,  rot: -1 },
            { col: "9 / span 4", row: 2, h: 540, ty: 60,   rot: 2 },
            { col: "1 / span 4", row: 3, h: 420, ty: 20,   rot: 1 },
            { col: "6 / span 5", row: 3, h: 500, ty: -30,  rot: -1.5 },
          ];
          const L = layouts[i % layouts.length];
          return (
            <Plate key={it.id} item={it} layout={L} scroll={scroll} index={i} navigate={navigate} />
          );
        })}
      </div>
    </div>
  );
}

function Plate({ item, layout, scroll, index, navigate }) {
  const ref = React.useRef(null);
  const [inView, setInView] = React.useState(0);
  const [hover, setHover] = React.useState(false);
  const [mouseInside, setMouseInside] = React.useState({ x: 0.5, y: 0.5 });

  React.useEffect(() => {
    if (!ref.current) return;
    const r = ref.current.getBoundingClientRect();
    const center = r.top + r.height / 2 - window.innerHeight / 2;
    setInView(-center * 0.18);
  }, [scroll]);

  const onMove = (e) => {
    if (!ref.current) return;
    const r = ref.current.getBoundingClientRect();
    setMouseInside({ x: (e.clientX - r.left) / r.width, y: (e.clientY - r.top) / r.height });
  };

  return (
    <div
      ref={ref}
      onMouseEnter={() => setHover(true)}
      onMouseLeave={() => setHover(false)}
      onMouseMove={onMove}
      onClick={() => navigate("ritual")}
      style={{
        gridColumn: layout.col, gridRow: layout.row,
        height: layout.h, position: "relative",
        transform: `translateY(${layout.ty + inView}px) rotate(${layout.rot}deg)`,
        transition: "transform 100ms linear",
        cursor: "pointer",
      }}>
      <div style={{
        position: "absolute", inset: 0,
        background: item.color,
        boxShadow: hover
          ? `0 40px 80px -20px ${item.accent}80, 0 0 0 1px rgba(60, 20, 24, 0.4)`
          : `0 20px 40px -16px rgba(20, 10, 8, 0.4)`,
        transition: "box-shadow 500ms cubic-bezier(0.2,0.8,0.2,1)",
        overflow: "hidden",
      }}>
        {/* Smoke layer */}
        <div aria-hidden style={{
          position: "absolute", inset: -40,
          background: `radial-gradient(circle at ${mouseInside.x * 100}% ${mouseInside.y * 100}%, ${item.accent}, transparent 60%)`,
          filter: "blur(20px)", opacity: hover ? 0.9 : 0.5,
          transition: "opacity 600ms",
        }}></div>

        {/* Paper grain inside plate */}
        <div aria-hidden style={{
          position: "absolute", inset: 0,
          backgroundImage: "url(\"data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='200' height='200'><filter id='n'><feTurbulence type='fractalNoise' baseFrequency='1.2' numOctaves='2'/></filter><rect width='100%' height='100%' filter='url(%23n)' opacity='0.5'/></svg>\")",
          mixBlendMode: "overlay", opacity: 0.4,
        }}></div>

        {/* Roman numeral huge */}
        <div style={{
          position: "absolute", left: -10, bottom: -40,
          fontFamily: "'Cormorant Garamond', serif", fontWeight: 700,
          fontSize: layout.h * 0.7, lineHeight: 0.85, color: "rgba(237, 230, 216, 0.94)",
          letterSpacing: "-0.04em", textShadow: `0 8px 24px rgba(0,0,0,0.4)`,
          transform: hover ? "scale(1.06) rotate(-2deg)" : "scale(1)",
          transformOrigin: "left bottom",
          transition: "transform 800ms cubic-bezier(0.2,0.8,0.2,1)",
        }}>{item.id}</div>

        {/* Top label */}
        <div style={{ position: "absolute", left: 24, top: 24, color: "rgba(237, 230, 216, 0.65)" }}>
          <div style={{ fontFamily: "'Cormorant Garamond', serif", fontSize: 11, letterSpacing: "0.42em", textTransform: "uppercase" }}>{item.tag}</div>
          <div style={{ fontFamily: "'Cormorant Garamond', serif", fontStyle: "italic", fontSize: 13, marginTop: 2 }}>{item.n}</div>
        </div>

        {/* Bottom label */}
        <div style={{ position: "absolute", right: 24, bottom: 24, textAlign: "right", color: "rgba(237, 230, 216, 0.96)" }}>
          <div style={{ fontFamily: "'Cormorant Garamond', serif", fontWeight: 700, fontSize: 32, lineHeight: 1, letterSpacing: "-0.02em" }}>{item.w}</div>
          <div style={{ fontFamily: "'Cormorant Garamond', serif", fontStyle: "italic", fontSize: 14, marginTop: 6, color: "rgba(237, 230, 216, 0.7)" }}>{item.sub}</div>
        </div>

        {/* Reveal arrow */}
        <div style={{
          position: "absolute", left: 24, bottom: 24,
          fontFamily: "'Inter', sans-serif", fontSize: 10, letterSpacing: "0.32em", textTransform: "uppercase",
          color: "rgba(237, 230, 216, 0.7)",
          transform: hover ? "translateX(8px)" : "translateX(0)",
          transition: "transform 320ms",
        }}>↗ ver prancha</div>
      </div>
    </div>
  );
}

/* CLOSING */
function KineticClosing({ scroll, setHoverWord }) {
  return (
    <div style={{ position: "relative", padding: "60px 0" }}>
      <div style={{ fontFamily: "'Cormorant Garamond', serif", fontSize: 12, letterSpacing: "0.42em", textTransform: "uppercase", color: "#7A1F2B", marginBottom: 60 }}>
        ¶ Coda
      </div>
      <div style={{
        fontFamily: "'Cormorant Garamond', serif", fontWeight: 400, fontStyle: "italic",
        fontSize: "clamp(80px, 12vw, 180px)", lineHeight: 0.95, letterSpacing: "-0.03em",
        color: "#1A1A1F", maxWidth: 1280, margin: "0 auto",
      }}>
        <HoverWord word="obrigado" setHoverWord={setHoverWord}>Obrigado</HoverWord> por <span style={{ color: "#7A1F2B" }}>chegar.</span>
      </div>
      <div style={{ fontFamily: "'Cormorant Garamond', serif", fontStyle: "italic", fontSize: 18, color: "#3a2a26", marginTop: 40 }}>
        — Rafael, Junho de 2026.
      </div>
      <div style={{ marginTop: 100, fontFamily: "'Cormorant Garamond', serif", fontSize: 11, letterSpacing: "0.42em", textTransform: "uppercase", color: "#3a2a26" }}>
        ✺ Folha N° XXX · Encerrada à mão. ✺
      </div>
    </div>
  );
}

/* ===== ATELIER (lista detalhada, formato livro) ===== */
function Atelier({ scroll, vh, setHoverWord, navigate }) {
  return (
    <section style={{ padding: "160px 40px 200px", maxWidth: 1280, margin: "0 auto" }}>
      <div style={{ fontFamily: "'Cormorant Garamond', serif", fontSize: 12, letterSpacing: "0.42em", textTransform: "uppercase", color: "#7A1F2B", marginBottom: 24 }}>
        Caderno II — Ateliê
      </div>
      <h2 style={{ fontFamily: "'Cormorant Garamond', serif", fontSize: "clamp(100px, 16vw, 220px)", fontWeight: 700, lineHeight: 0.85, margin: 0, color: "#1A1A1F" }}>
        As <em style={{ color: "#7A1F2B", fontWeight: 700 }}>oito</em><br/>pranchas.
      </h2>

      <div style={{ marginTop: 120, display: "grid", gap: 80 }}>
        {ITEMS.map((it, i) => (
          <AtelierEntry key={it.id} item={it} index={i} scroll={scroll} vh={vh} navigate={navigate} />
        ))}
      </div>
    </section>
  );
}

function AtelierEntry({ item, index, scroll, vh, navigate }) {
  const ref = React.useRef(null);
  const [parallax, setParallax] = React.useState(0);
  React.useEffect(() => {
    if (!ref.current) return;
    const r = ref.current.getBoundingClientRect();
    const center = r.top + r.height / 2 - window.innerHeight / 2;
    setParallax(-center * 0.12);
  }, [scroll]);

  const flip = index % 2 === 1;

  return (
    <article ref={ref} style={{
      display: "grid", gridTemplateColumns: flip ? "1fr 1.4fr" : "1.4fr 1fr",
      gap: 60, alignItems: "center",
    }}>
      <div style={{
        order: flip ? 2 : 1,
        height: 540, position: "relative",
        transform: `translateY(${parallax}px) rotate(${flip ? 1.5 : -1.5}deg)`,
        transition: "transform 100ms linear",
      }}>
        <div style={{
          position: "absolute", inset: 0, background: item.color,
          boxShadow: `0 30px 60px -20px ${item.accent}80`, overflow: "hidden",
        }}>
          <div style={{
            position: "absolute", inset: -30,
            background: `radial-gradient(circle at 30% 30%, ${item.accent}88, transparent 60%)`,
            filter: "blur(20px)",
          }}></div>
          <div style={{
            position: "absolute", left: -10, bottom: -50,
            fontFamily: "'Cormorant Garamond', serif", fontWeight: 700,
            fontSize: 380, lineHeight: 0.85,
            color: "rgba(237, 230, 216, 0.95)",
          }}>{item.id}</div>
          <div style={{ position: "absolute", left: 24, top: 24, color: "rgba(237, 230, 216, 0.7)" }}>
            <div style={{ fontFamily: "'Cormorant Garamond', serif", fontSize: 11, letterSpacing: "0.42em", textTransform: "uppercase" }}>{item.tag}</div>
            <div style={{ fontFamily: "'Cormorant Garamond', serif", fontStyle: "italic", fontSize: 13, marginTop: 2 }}>{item.n}</div>
          </div>
        </div>
      </div>

      <div style={{ order: flip ? 1 : 2 }}>
        <div style={{ fontFamily: "'Cormorant Garamond', serif", fontSize: 12, letterSpacing: "0.42em", textTransform: "uppercase", color: "#7A1F2B", marginBottom: 14 }}>
          Prancha {item.id} — {item.tag}
        </div>
        <h3 style={{
          fontFamily: "'Cormorant Garamond', serif", fontWeight: 700,
          fontSize: 88, lineHeight: 0.95, letterSpacing: "-0.03em", margin: 0, color: "#1A1A1F",
        }}>{item.w}</h3>
        <div style={{
          fontFamily: "'Cormorant Garamond', serif", fontStyle: "italic",
          fontSize: 22, color: "#7A1F2B", marginTop: 8,
        }}>{item.sub}</div>
        <p style={{
          fontFamily: "'Inter', sans-serif", fontSize: 15, color: "#3a2a26",
          lineHeight: 1.7, marginTop: 28, maxWidth: 460,
        }}>
          Item escolhido com calma. Se você sentir vontade de marcá-lo no seu nome, é só seguir para o ritual — uma página silenciosa onde se confirma a reserva.
        </p>
        <button onClick={() => navigate("ritual")} style={{ ...editorialBtn, marginTop: 36 }} className="ed-btn">
          Reservar esta prancha <span className="ed-arrow" style={{ display: "inline-block", marginLeft: 18, transition: "transform 320ms" }}>—→</span>
        </button>
      </div>
    </article>
  );
}

/* ===== RITUAL (Pix + reserva) ===== */
function Ritual({ scroll, vh, setHoverWord }) {
  const [amount, setAmount] = React.useState(150);
  const [copied, setCopied] = React.useState(false);
  return (
    <section style={{ padding: "160px 40px 200px", maxWidth: 1280, margin: "0 auto" }}>
      <div style={{ fontFamily: "'Cormorant Garamond', serif", fontSize: 12, letterSpacing: "0.42em", textTransform: "uppercase", color: "#7A1F2B", marginBottom: 24 }}>
        Caderno III — Ritual
      </div>
      <h2 style={{ fontFamily: "'Cormorant Garamond', serif", fontSize: "clamp(100px, 16vw, 220px)", fontWeight: 700, lineHeight: 0.85, margin: 0, color: "#1A1A1F" }}>
        O <em style={{ color: "#7A1F2B", fontWeight: 700 }}>gesto</em><br/>final.
      </h2>
      <p style={{ fontFamily: "'Cormorant Garamond', serif", fontStyle: "italic", fontSize: 24, color: "#3a2a26", marginTop: 32, maxWidth: 700, lineHeight: 1.4 }}>
        Sem cadastro, sem pressa. Você manda um valor pelo Pix — qualquer valor — e eu somo às outras contribuições. É só isso.
      </p>

      <div style={{ marginTop: 100, display: "grid", gridTemplateColumns: "1fr 1fr", gap: 60, alignItems: "start" }}>
        {/* QR + dados */}
        <div style={{
          background: "rgba(237, 230, 216, 0.5)", border: "1px solid rgba(60, 20, 24, 0.18)",
          padding: 40, position: "relative",
        }}>
          <div style={{ fontFamily: "'Cormorant Garamond', serif", fontSize: 12, letterSpacing: "0.42em", textTransform: "uppercase", color: "#7A1F2B", marginBottom: 28 }}>I — A chave</div>
          <div style={{ display: "grid", gridTemplateColumns: "1fr 200px", gap: 32 }}>
            <div style={{ display: "grid", gap: 18 }}>
              {[["A quem", "Rafael S. Andrade"], ["Chave (e-mail)", "rafael.aniv@email.com"], ["Banco", "260 — Nu Pagamentos"], ["Cidade", "São Paulo"]].map(([k, v]) => (
                <div key={k}>
                  <div style={{ fontFamily: "'Cormorant Garamond', serif", fontStyle: "italic", fontSize: 13, color: "#7A1F2B" }}>{k}</div>
                  <div style={{ fontFamily: "'Cormorant Garamond', serif", fontSize: 22, color: "#1A1A1F", marginTop: 2 }}>{v}</div>
                </div>
              ))}
            </div>
            <div style={{ background: "#EDE6D8", padding: 8, aspectRatio: "1", display: "grid", gridTemplateColumns: "repeat(14, 1fr)", gridTemplateRows: "repeat(14, 1fr)", gap: 1, position: "relative", overflow: "hidden", border: "1px solid rgba(60,20,24,0.2)" }}>
              {Array.from({ length: 196 }).map((_, i) => {
                const on = (i * 37 + (i % 11) * 7) % 5 < 2;
                const r = i % 14, c = Math.floor(i / 14);
                const corner = (r < 3 && c < 3) || (r >= 11 && c < 3) || (r < 3 && c >= 11);
                return <div key={i} style={{ background: corner || on ? "#1A1A1F" : "transparent" }}></div>;
              })}
              <div aria-hidden style={{ position: "absolute", left: 0, right: 0, top: 0, height: 2, background: "#7A1F2B", animation: "qrScan 3s linear infinite" }}></div>
            </div>
          </div>
          <div style={{ marginTop: 32, paddingTop: 24, borderTop: "1px solid rgba(60,20,24,0.15)" }}>
            <div style={{ fontFamily: "'Cormorant Garamond', serif", fontStyle: "italic", fontSize: 13, color: "#7A1F2B", marginBottom: 10 }}>copia e cola</div>
            <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 11, lineHeight: 1.5, padding: 14, background: "rgba(60,20,24,0.06)", color: "#1A1A1F", wordBreak: "break-all" }}>
              00020126360014BR.GOV.BCB.PIX0114rafael.aniv@email5204000053039865406150.005802BR5919RAFAEL S ANDRADE6009SAO PAULO62070503***6304B7C9
            </div>
            <button onClick={() => { setCopied(true); setTimeout(() => setCopied(false), 1500); }} style={{
              marginTop: 14, width: "100%", background: copied ? "#7A1F2B" : "#1A1A1F",
              color: "#EDE6D8", border: "none", padding: "16px",
              fontFamily: "'Cormorant Garamond', serif", fontSize: 15, letterSpacing: "0.18em", textTransform: "uppercase",
              cursor: "pointer", transition: "background 240ms",
            }}>{copied ? "✓ código copiado" : "copiar código"}</button>
          </div>
        </div>

        {/* Valor + recado */}
        <div style={{
          background: "rgba(237, 230, 216, 0.5)", border: "1px solid rgba(60, 20, 24, 0.18)",
          padding: 40,
        }}>
          <div style={{ fontFamily: "'Cormorant Garamond', serif", fontSize: 12, letterSpacing: "0.42em", textTransform: "uppercase", color: "#7A1F2B", marginBottom: 28 }}>II — O valor</div>
          <div style={{ display: "flex", alignItems: "baseline", gap: 12, paddingBottom: 12, borderBottom: "1px solid #1A1A1F" }}>
            <span style={{ fontFamily: "'Cormorant Garamond', serif", fontSize: 32, color: "#7A1F2B", fontStyle: "italic" }}>R$</span>
            <input type="number" value={amount} onChange={(e) => setAmount(Number(e.target.value) || 0)} style={{
              flex: 1, background: "transparent", border: "none", outline: "none",
              fontFamily: "'Cormorant Garamond', serif", fontWeight: 700, fontSize: 110,
              letterSpacing: "-0.04em", color: "#1A1A1F", padding: 0, lineHeight: 1,
            }} />
          </div>
          <div style={{ display: "flex", flexWrap: "wrap", gap: 6, marginTop: 18 }}>
            {[50, 100, 150, 250, 500, 1000].map((v) => (
              <button key={v} onClick={() => setAmount(v)} style={{
                padding: "8px 14px", fontFamily: "'Cormorant Garamond', serif", fontStyle: "italic", fontSize: 14,
                background: amount === v ? "#1A1A1F" : "transparent",
                color: amount === v ? "#EDE6D8" : "#1A1A1F",
                border: "1px solid " + (amount === v ? "#1A1A1F" : "rgba(60,20,24,0.3)"),
                cursor: "pointer", transition: "all 240ms",
              }}>R$ {v}</button>
            ))}
          </div>

          <div style={{ marginTop: 36, display: "grid", gap: 22 }}>
            {[["Como você assina", "voce@assina"], ["Seu e-mail (pra eu te agradecer)", "voce@exemplo"], ["Quer deixar um recado?", "Parabéns, mano…"]].map(([l, p]) => (
              <div key={l}>
                <label style={{ fontFamily: "'Cormorant Garamond', serif", fontStyle: "italic", fontSize: 14, color: "#7A1F2B" }}>{l}</label>
                <input placeholder={p} style={{
                  width: "100%", marginTop: 4, padding: "10px 0",
                  background: "transparent", border: "none", borderBottom: "1px solid rgba(60,20,24,0.3)",
                  fontFamily: "'Cormorant Garamond', serif", fontSize: 18, color: "#1A1A1F", outline: "none",
                }} />
              </div>
            ))}
          </div>

          <button style={{
            marginTop: 36, width: "100%", background: "#7A1F2B", color: "#EDE6D8",
            border: "none", padding: "18px", fontFamily: "'Cormorant Garamond', serif",
            fontStyle: "italic", fontSize: 20, cursor: "pointer", letterSpacing: "0.04em",
          }}>Confirmar o gesto →</button>
        </div>
      </div>
    </section>
  );
}

/* ===== INK MASK PAGE TRANSITION ===== */
function InkMask({ phase }) {
  if (!phase) return null;
  return (
    <div aria-hidden style={{
      position: "fixed", inset: 0, zIndex: 9000, pointerEvents: "none",
      animation: phase === "out" ? "inkIn 700ms cubic-bezier(0.7, 0, 0.3, 1) forwards" : "inkOut 800ms cubic-bezier(0.16, 1, 0.3, 1) forwards",
    }}>
      <svg viewBox="0 0 100 100" preserveAspectRatio="none" style={{ width: "100%", height: "100%" }}>
        <defs>
          <radialGradient id="inkgrad" cx="50%" cy="50%" r="60%">
            <stop offset="0%" stopColor="#1A1A1F" />
            <stop offset="60%" stopColor="#1A1A1F" />
            <stop offset="100%" stopColor="rgba(26,26,31,0.3)" />
          </radialGradient>
          <filter id="bumpy">
            <feTurbulence type="fractalNoise" baseFrequency="0.04" numOctaves="2" />
            <feDisplacementMap in="SourceGraphic" scale="30" />
          </filter>
        </defs>
        <rect width="100" height="100" fill="url(#inkgrad)" filter="url(#bumpy)" />
      </svg>
    </div>
  );
}

/* ===== SHARED ===== */
const editorialBtn = {
  background: "transparent", color: "#1A1A1F",
  border: "none", padding: 0,
  fontFamily: "'Cormorant Garamond', serif", fontStyle: "italic",
  fontSize: 22, letterSpacing: "0.02em", cursor: "pointer",
  borderBottom: "1px solid #1A1A1F", paddingBottom: 6,
  display: "inline-flex", alignItems: "center",
};

function GlobalStyles() {
  return <style>{`
    * { box-sizing: border-box; cursor: none; }
    a, button, [role="button"] { cursor: none; }
    @keyframes inkIn { from { clip-path: circle(0% at 50% 50%); } to { clip-path: circle(150% at 50% 50%); } }
    @keyframes inkOut { from { clip-path: circle(150% at 50% 50%); } to { clip-path: circle(0% at 50% 50%); } }
    @keyframes slideRight { 0% { transform: translateX(-100%); } 100% { transform: translateX(300%); } }
    @keyframes qrScan { 0% { transform: translateY(0); } 100% { transform: translateY(180px); } }
    .ed-btn:hover .ed-arrow { transform: translateX(12px); }
    @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: 0.001ms !important; transition-duration: 0.001ms !important; } * { cursor: auto !important; } }
    ::selection { background: #7A1F2B; color: #EDE6D8; }
    input::placeholder { color: rgba(60,20,24,0.4); font-style: italic; }
    body { margin: 0; font-family: 'Cormorant Garamond', serif; }
    html { scroll-behavior: auto; }
  `}</style>;
}

window.App = App;
