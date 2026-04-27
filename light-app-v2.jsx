/* LIGHT V4 — clean editorial + roxo accent + mais movimento, linguagem natural */

const GIFTS_V2 = [
  { id: 1,  name: "Fone Sennheiser HD 660S2",     cat: "Áudio",     price: 4290, qty: 1, top: true,  desc: "Fone de ouvido aberto. Pra ouvir disco com calma.", color: "#1B3A8B", accent: "#6B3FCC" },
  { id: 2,  name: "Tênis Salomon XT-6",            cat: "Tênis",     price: 1899, qty: 2, top: false, desc: "Trail running. Serve pra cidade também.",                color: "#2A2A2A", accent: "#1B3A8B" },
  { id: 3,  name: "Whisky Yamazaki 12 anos",       cat: "Bar",       price: 2890, qty: 1, top: true,  desc: "Single malt japonês. Pra domingos sem pressa.",         color: "#7A4B1F", accent: "#6B3FCC" },
  { id: 4,  name: "Relógio Moonswatch Mars",       cat: "Relógios",  price: 2300, qty: 0, top: false, desc: "Bioceramic. Edição vermelha do Snoopy.",                color: "#A03426", accent: "#1B3A8B" },
  { id: 5,  name: "Cadeira Herman Miller Aeron",   cat: "Trabalho",  price: 8900, qty: 1, top: false, desc: "Tamanho B, com apoio lombar. Pra terminar o doutorado.",color: "#3F3F3F", accent: "#6B3FCC" },
  { id: 6,  name: "Câmera Ricoh GR IIIx",          cat: "Foto",      price: 7990, qty: 1, top: false, desc: "APS-C, 40mm. A câmera de bolso definitiva.",            color: "#0F0F12", accent: "#6B3FCC" },
];

const COLORS = {
  bg: "#FAFAF7",
  ink: "#0F0F12",
  paper: "#FFFFFF",
  mute: "#6b6b73",
  soft: "#43434b",
  blue: "#1B3A8B",
  purple: "#6B3FCC",
  purpleSoft: "#EFE9FA",
  blueSoft: "#EEF1F9",
  line: "rgba(15,15,18,0.08)",
};

function App() {
  const [route, setRoute] = React.useState("home");
  const [cat, setCat] = React.useState("Todos");
  const [authOpen, setAuthOpen] = React.useState(false);
  const [authTab, setAuthTab] = React.useState("signup");
  const [pixCopied, setPixCopied] = React.useState(false);
  const [pixAmount, setPixAmount] = React.useState(150);
  const [reservingGift, setReservingGift] = React.useState(GIFTS_V2[0]);

  const target = React.useMemo(() => new Date(Date.now() + (47 * 86400 + 8 * 3600 + 23 * 60) * 1000), []);
  const [now, setNow] = React.useState(Date.now());
  React.useEffect(() => { const t = setInterval(() => setNow(Date.now()), 1000); return () => clearInterval(t); }, []);
  const diff = Math.max(0, target.getTime() - now);
  const dd = Math.floor(diff / 86400000);
  const hh = Math.floor((diff % 86400000) / 3600000);
  const mm = Math.floor((diff % 3600000) / 60000);
  const ss = Math.floor((diff % 60000) / 1000);

  // Mouse parallax
  const [mouse, setMouse] = React.useState({ x: 0.5, y: 0.5 });
  React.useEffect(() => {
    const onMove = (e) => setMouse({ x: e.clientX / window.innerWidth, y: e.clientY / window.innerHeight });
    window.addEventListener("mousemove", onMove);
    return () => window.removeEventListener("mousemove", onMove);
  }, []);

  const cats = ["Todos", "Áudio", "Tênis", "Bar", "Relógios", "Trabalho", "Foto"];
  const list = cat === "Todos" ? GIFTS_V2 : GIFTS_V2.filter((g) => g.cat === cat);

  const openReserve = (g) => { setReservingGift(g); setAuthOpen(true); };

  return (
    <div style={{ background: COLORS.bg, color: COLORS.ink, minHeight: "100vh", fontFamily: "'Inter', system-ui, sans-serif", overflow: "hidden auto" }}>
      <Header route={route} setRoute={setRoute} />
      <div key={route} style={{ animation: "pageIn 520ms cubic-bezier(0.16,1,0.3,1)" }}>
        {route === "home"  && <Home dd={dd} hh={hh} mm={mm} ss={ss} setRoute={setRoute} mouse={mouse} openReserve={openReserve} />}
        {route === "gifts" && <Gifts cats={cats} cat={cat} setCat={setCat} list={list} onReserve={openReserve} />}
        {route === "pix"   && <Pix amount={pixAmount} setAmount={setPixAmount} copied={pixCopied} onCopy={() => { setPixCopied(true); setTimeout(() => setPixCopied(false), 1500); }} />}
        {route === "my"    && <My />}
      </div>
      <Footer />
      {authOpen && <Auth tab={authTab} setTab={setAuthTab} gift={reservingGift} onClose={() => setAuthOpen(false)} />}
      <Anim />
    </div>
  );
}

/* ===== HEADER ===== */
function Header({ route, setRoute }) {
  const items = [["home", "Início"], ["gifts", "Lista"], ["pix", "Pix"], ["my", "Minhas reservas"]];
  return (
    <header style={{
      position: "sticky", top: 0, zIndex: 50,
      background: "rgba(250,250,247,0.78)", backdropFilter: "blur(20px)",
      borderBottom: "1px solid " + COLORS.line,
      padding: "18px 56px", display: "grid", gridTemplateColumns: "1fr auto 1fr", alignItems: "center",
    }}>
      <div style={{ fontWeight: 600, fontSize: 12, letterSpacing: "0.18em", textTransform: "uppercase", color: COLORS.mute }}>
        São Paulo · 12 de junho, 2026
      </div>
      <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
        <span style={{
          width: 30, height: 30, background: COLORS.ink, color: COLORS.bg,
          display: "inline-flex", alignItems: "center", justifyContent: "center",
          fontWeight: 700, fontSize: 14, borderRadius: 4, position: "relative", overflow: "hidden",
        }}>
          R
          <span style={{ position: "absolute", inset: 0, background: "linear-gradient(135deg, transparent 30%, rgba(107,63,204,0.6) 100%)" }}></span>
        </span>
        <span style={{ fontWeight: 600, fontSize: 16, letterSpacing: "-0.01em" }}>Rafael, 30.</span>
      </div>
      <nav style={{ display: "flex", gap: 4, justifyContent: "flex-end" }}>
        {items.map(([k, l]) => (
          <button key={k} onClick={() => setRoute(k)} style={{
            padding: "10px 16px", fontSize: 14, fontWeight: 500,
            background: "transparent", color: route === k ? COLORS.ink : COLORS.mute,
            border: "none", cursor: "pointer", position: "relative",
            fontFamily: "inherit", transition: "color 200ms ease",
          }}>
            {l}
            {route === k && (
              <span style={{
                position: "absolute", left: 16, right: 16, bottom: 4, height: 2,
                background: "linear-gradient(90deg, " + COLORS.ink + " 50%, " + COLORS.purple + " 50%)",
                animation: "slideUnderline 360ms cubic-bezier(0.2,0.8,0.2,1)",
              }}></span>
            )}
          </button>
        ))}
      </nav>
    </header>
  );
}

/* ===== HOME ===== */
function Home({ dd, hh, mm, ss, setRoute, mouse, openReserve }) {
  return (
    <main>
      {/* HERO */}
      <section style={{ padding: "72px 56px 56px", display: "grid", gridTemplateColumns: "1.2fr 1fr", gap: 40, alignItems: "stretch", position: "relative" }}>
        {/* Floating decorative blobs */}
        <div aria-hidden style={{
          position: "absolute", top: 80, left: -120, width: 360, height: 360, borderRadius: "50%",
          background: "radial-gradient(circle, rgba(107,63,204,0.18), transparent 70%)",
          filter: "blur(40px)", animation: "float1 14s ease-in-out infinite", pointerEvents: "none",
        }}></div>

        <div style={{ display: "flex", flexDirection: "column", justifyContent: "space-between", position: "relative", zIndex: 2 }}>
          <div>
            <div style={{ display: "inline-flex", gap: 10, alignItems: "center", marginBottom: 28, padding: "6px 12px", background: COLORS.purpleSoft, borderRadius: 999 }}>
              <span style={{ width: 6, height: 6, background: COLORS.purple, borderRadius: 999, animation: "pulse 2s ease-in-out infinite" }}></span>
              <span style={{ fontWeight: 600, fontSize: 11, letterSpacing: "0.16em", textTransform: "uppercase", color: COLORS.purple }}>
                Faltam {dd} dias
              </span>
            </div>
            <h1 style={{ fontWeight: 700, fontSize: 144, letterSpacing: "-0.06em", lineHeight: 0.88, margin: 0, color: COLORS.ink }}>
              Trinta anos<br />
              de <span style={{ position: "relative", display: "inline-block" }}>
                boas
                <svg viewBox="0 0 220 22" preserveAspectRatio="none" style={{
                  position: "absolute", left: -4, right: -4, bottom: -2, width: "calc(100% + 8px)", height: 22,
                  pointerEvents: "none",
                }}>
                  <path d="M2,12 Q60,2 120,11 T218,9" stroke={COLORS.purple} strokeWidth="6" strokeLinecap="round" fill="none"
                    strokeDasharray="500" strokeDashoffset="500" style={{ animation: "drawSquiggle 1.4s 0.6s cubic-bezier(0.2,0.8,0.2,1) forwards" }} />
                </svg>
              </span><br />
              escolhas.
            </h1>
            <p style={{ fontSize: 18, color: COLORS.soft, marginTop: 32, maxWidth: 480, lineHeight: 1.55 }}>
              Separei oito coisas que tô considerando faz tempo. Se quiser dar uma, escolhe na lista. Se preferir mandar um valor pelo Pix, também rola — sem cerimônia.
            </p>
            <div style={{ display: "flex", gap: 12, marginTop: 36 }}>
              <button onClick={() => setRoute("gifts")} className="btn-magnetic" style={btn.solid}>
                Ver a lista <span style={{ marginLeft: 10, display: "inline-block", transition: "transform 240ms ease" }} className="btn-arrow">→</span>
              </button>
              <button onClick={() => setRoute("pix")} style={btn.ghost}>Mandar pelo Pix</button>
            </div>
          </div>

          <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", marginTop: 56, paddingTop: 24, borderTop: "1px solid " + COLORS.line }}>
            {[["08", "presentes"], ["02", "reservados"], ["47%", "do total"], ["07", "dias pra confirmar"]].map(([v, l], i) => (
              <div key={l} style={{ animation: `fadeUp 600ms ${i * 80}ms both cubic-bezier(0.2,0.8,0.2,1)` }}>
                <div style={{ fontWeight: 700, fontSize: 32, letterSpacing: "-0.03em", color: i === 2 ? COLORS.purple : COLORS.ink }}>{v}</div>
                <div style={{ fontSize: 12, color: COLORS.mute, marginTop: 4 }}>{l}</div>
              </div>
            ))}
          </div>
        </div>

        {/* Cover */}
        <CoverArt dd={dd} hh={hh} mm={mm} ss={ss} mouse={mouse} />
      </section>

      {/* MARQUEE */}
      <section style={{ borderTop: "1px solid " + COLORS.line, borderBottom: "1px solid " + COLORS.line, padding: "20px 0", overflow: "hidden", background: COLORS.paper }}>
        <div style={{ display: "flex", gap: 48, animation: "marquee 36s linear infinite", whiteSpace: "nowrap", width: "max-content" }}>
          {Array.from({ length: 4 }).flatMap((_, i) => [
            "✺ Trinta anos",
            "ano da virada",
            "★ doze de junho",
            "sem pressão, sem firula",
            "✺ obrigado por estar aqui",
            "Rafael, 2026",
          ].map((t, j) => (
            <span key={`${i}-${j}`} style={{ fontWeight: 600, fontSize: 22, letterSpacing: "-0.01em", color: j % 3 === 0 ? COLORS.purple : COLORS.ink }}>{t}</span>
          )))}
        </div>
      </section>

      {/* FEATURED */}
      <section style={{ padding: "72px 56px 56px" }}>
        <div style={{ display: "flex", alignItems: "flex-end", justifyContent: "space-between", marginBottom: 32 }}>
          <div>
            <div style={{ fontSize: 12, fontWeight: 600, letterSpacing: "0.18em", color: COLORS.mute, textTransform: "uppercase", marginBottom: 12 }}>Em destaque</div>
            <h2 style={{ fontWeight: 700, fontSize: 56, letterSpacing: "-0.04em", margin: 0 }}>
              O que eu quero <em style={{ fontFamily: "Inter", fontStyle: "italic", color: COLORS.purple, fontWeight: 700 }}>mesmo</em>.
            </h2>
          </div>
          <button onClick={() => setRoute("gifts")} style={{
            background: "transparent", border: "none", color: COLORS.ink,
            fontWeight: 600, fontSize: 14, cursor: "pointer", borderBottom: "1px solid " + COLORS.ink, paddingBottom: 4,
          }}>Ver os 8 →</button>
        </div>

        <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 20 }}>
          {GIFTS_V2.slice(0, 3).map((g, i) => <Card key={g.id} g={g} big delay={i * 100} onReserve={() => openReserve(g)} />)}
        </div>
      </section>

      {/* THANK-YOU NOTE — creative section */}
      <section style={{ padding: "40px 56px 96px" }}>
        <div style={{
          background: COLORS.ink, color: COLORS.bg, borderRadius: 6, padding: "72px 56px",
          position: "relative", overflow: "hidden",
        }}>
          <div aria-hidden style={{
            position: "absolute", right: -100, top: -100, width: 420, height: 420, borderRadius: "50%",
            background: "radial-gradient(circle, rgba(107,63,204,0.55), transparent 65%)",
            filter: "blur(20px)", animation: "float2 18s ease-in-out infinite",
          }}></div>
          <div style={{ position: "relative", zIndex: 2, display: "grid", gridTemplateColumns: "1fr 1fr", gap: 48, alignItems: "center" }}>
            <div>
              <div style={{ fontSize: 12, fontWeight: 600, letterSpacing: "0.18em", color: "rgba(255,255,255,0.6)", textTransform: "uppercase", marginBottom: 18 }}>
                Recadinho
              </div>
              <p style={{ fontSize: 36, lineHeight: 1.25, letterSpacing: "-0.025em", margin: 0, fontWeight: 500 }}>
                "Não tô esperando presente. Mas se tiver afim de mandar alguma coisa, esses são os candidatos. Vir comigo no dia já é mais que suficiente."
              </p>
              <div style={{ marginTop: 28, fontSize: 14, color: "rgba(255,255,255,0.7)" }}>— Rafa</div>
            </div>
            <div style={{ position: "relative", height: 280 }}>
              {[
                { l: 24, t: 30, w: 110, c: COLORS.purple, r: -8 },
                { l: 150, t: 70, w: 120, c: COLORS.bg,   r: 6 },
                { l: 80, t: 150, w: 130, c: "#1B3A8B",   r: -3 },
              ].map((p, i) => (
                <div key={i} style={{
                  position: "absolute", left: p.l, top: p.t, width: p.w, height: p.w * 1.35,
                  background: p.c, borderRadius: 4, transform: `rotate(${p.r}deg)`,
                  boxShadow: "0 20px 40px rgba(0,0,0,0.35)",
                  animation: `floatCard 8s ${i * 0.6}s ease-in-out infinite`,
                }}></div>
              ))}
            </div>
          </div>
        </div>
      </section>
    </main>
  );
}

/* Cover composition with parallax + animated countdown */
function CoverArt({ dd, hh, mm, ss, mouse }) {
  const px = (mouse.x - 0.5) * 16;
  const py = (mouse.y - 0.5) * 16;
  return (
    <div style={{
      position: "relative", borderRadius: 6, overflow: "hidden",
      background: "linear-gradient(160deg, " + COLORS.blue + " 0%, " + COLORS.ink + " 70%, " + COLORS.purple + " 130%)",
      minHeight: 640, color: "#fff",
    }}>
      <div aria-hidden style={{
        position: "absolute", inset: 0,
        backgroundImage: "radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px)",
        backgroundSize: "4px 4px",
      }}></div>
      <div aria-hidden style={{
        position: "absolute", left: -80 + px * 2, top: -80 + py * 2, width: 320, height: 320, borderRadius: "50%",
        background: "radial-gradient(circle, rgba(107,63,204,0.65), transparent 70%)", filter: "blur(20px)",
      }}></div>
      <div aria-hidden style={{
        position: "absolute", right: -120 - px, bottom: -120 - py, width: 380, height: 380, borderRadius: "50%",
        background: "radial-gradient(circle, rgba(255,255,255,0.16), transparent 65%)", filter: "blur(40px)",
      }}></div>

      <div style={{ position: "absolute", left: 24, top: 24, color: "rgba(255,255,255,0.7)", fontSize: 11, letterSpacing: "0.18em", fontWeight: 600, textTransform: "uppercase" }}>
        Faltam apenas
      </div>
      <div style={{ position: "absolute", right: 24, top: 24, color: "rgba(255,255,255,0.7)", fontSize: 11, letterSpacing: "0.18em", fontWeight: 600, textTransform: "uppercase" }}>
        12 · 06 · 26
      </div>

      {/* Concentric rings */}
      <svg viewBox="0 0 400 400" style={{ position: "absolute", left: "50%", top: "50%", width: 580, height: 580, transform: `translate(-50%,-50%) translate(${px}px, ${py}px)` }}>
        {[160, 130, 100, 70].map((r, i) => (
          <circle key={i} cx="200" cy="200" r={r} fill="none"
            stroke="rgba(255,255,255,0.16)" strokeWidth="1"
            strokeDasharray={`${r * 0.4} ${r * 0.2}`}
            style={{ transformOrigin: "200px 200px", animation: `spin ${28 + i * 6}s linear ${i % 2 ? "reverse" : "normal"} infinite` }} />
        ))}
      </svg>

      <div style={{ position: "absolute", left: 32, bottom: 40, transform: `translate(${px * 0.4}px, ${py * 0.4}px)` }}>
        <div style={{ fontWeight: 700, fontSize: 256, letterSpacing: "-0.07em", lineHeight: 0.85, color: "#fff" }}>
          {String(dd).padStart(2, "0")}
        </div>
        <div style={{ fontSize: 14, color: "rgba(255,255,255,0.7)", marginTop: 4 }}>dias até chegar.</div>
      </div>

      <div style={{ position: "absolute", right: 32, bottom: 40, textAlign: "right" }}>
        <div style={{ display: "flex", gap: 18 }}>
          {[["h", hh], ["m", mm], ["s", ss]].map(([l, v], i) => (
            <div key={l}>
              <div style={{ fontWeight: 600, fontSize: 28, letterSpacing: "-0.02em",
                color: i === 2 ? COLORS.purple : "#fff",
                textShadow: i === 2 ? "0 0 20px rgba(107,63,204,0.6)" : "none",
              }}>{String(v).padStart(2, "0")}</div>
              <div style={{ fontSize: 11, color: "rgba(255,255,255,0.6)", marginTop: 2, letterSpacing: "0.16em", textTransform: "uppercase" }}>{l}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

/* ===== CARD ===== */
function Card({ g, big = false, onReserve = () => {}, delay = 0 }) {
  const [hover, setHover] = React.useState(false);
  const ref = React.useRef(null);
  const [tilt, setTilt] = React.useState({ x: 0, y: 0 });
  const isOut = g.qty === 0;

  const onMove = (e) => {
    if (!ref.current) return;
    const r = ref.current.getBoundingClientRect();
    const tx = ((e.clientX - r.left) / r.width - 0.5) * 6;
    const ty = ((e.clientY - r.top) / r.height - 0.5) * -6;
    setTilt({ x: tx, y: ty });
  };
  return (
    <article
      ref={ref}
      onMouseEnter={() => setHover(true)}
      onMouseLeave={() => { setHover(false); setTilt({ x: 0, y: 0 }); }}
      onMouseMove={onMove}
      style={{
        background: COLORS.paper, border: "1px solid " + COLORS.line, borderRadius: 6,
        overflow: "hidden", transition: "transform 320ms cubic-bezier(0.2,0.8,0.2,1), box-shadow 320ms ease, border-color 200ms ease",
        transform: hover ? `translateY(-6px) perspective(1000px) rotateY(${tilt.x}deg) rotateX(${tilt.y}deg)` : "translateY(0)",
        boxShadow: hover ? `0 30px 60px -20px rgba(15,15,18,0.22), 0 0 0 1px ${g.accent}33` : "0 1px 0 rgba(15,15,18,0.03)",
        opacity: isOut ? 0.55 : 1, position: "relative",
        animation: `fadeUp 600ms ${delay}ms both cubic-bezier(0.2,0.8,0.2,1)`,
      }}>
      <div style={{ position: "relative", aspectRatio: big ? "4/5" : "5/6", background: g.color, overflow: "hidden" }}>
        <div aria-hidden style={{
          position: "absolute", inset: 0,
          backgroundImage: "radial-gradient(rgba(255,255,255,0.05) 1px, transparent 1px)",
          backgroundSize: "5px 5px", opacity: 0.5,
        }}></div>
        {/* Animated accent halo on hover */}
        <div aria-hidden style={{
          position: "absolute", inset: -40, background: `radial-gradient(circle at 30% 30%, ${g.accent}66, transparent 60%)`,
          opacity: hover ? 1 : 0, transition: "opacity 500ms ease", filter: "blur(20px)",
        }}></div>
        <div style={{
          position: "absolute", inset: 0, display: "flex", alignItems: "center", justifyContent: "center",
          fontWeight: 700, color: "rgba(255,255,255,0.94)",
          fontSize: big ? 200 : 140, letterSpacing: "-0.06em",
          textShadow: "0 8px 32px rgba(0,0,0,0.25)",
          transform: hover ? "scale(1.06) rotate(-1deg)" : "scale(1)",
          transition: "transform 700ms cubic-bezier(0.2,0.8,0.2,1)",
        }}>{g.name.split(" ").pop()[0]}</div>
        {g.top && !isOut && (
          <div style={{
            position: "absolute", top: 14, right: 14, background: COLORS.purple, color: "#fff",
            padding: "5px 10px", fontSize: 11, fontWeight: 600, letterSpacing: "0.12em", textTransform: "uppercase", borderRadius: 4,
            boxShadow: "0 4px 16px rgba(107,63,204,0.4)",
          }}>★ favorito</div>
        )}
        {isOut && (
          <div style={{
            position: "absolute", top: 14, right: 14, background: "rgba(15,15,18,0.85)", color: "#fff",
            padding: "5px 10px", fontSize: 11, fontWeight: 600, letterSpacing: "0.12em", textTransform: "uppercase", borderRadius: 4,
          }}>já reservaram</div>
        )}
      </div>
      <div style={{ padding: 22 }}>
        <div style={{ display: "flex", justifyContent: "space-between", marginBottom: 8, fontSize: 11, color: COLORS.mute, fontWeight: 600, letterSpacing: "0.14em", textTransform: "uppercase" }}>
          <span>n° {String(g.id).padStart(2, "0")} · {g.cat}</span>
          <span style={{ color: isOut ? "#a03426" : g.accent }}>{isOut ? "indisponível" : `${g.qty} ${g.qty === 1 ? "vaga" : "vagas"}`}</span>
        </div>
        <h3 style={{ fontWeight: 600, fontSize: big ? 22 : 18, margin: "0 0 8px", letterSpacing: "-0.02em", lineHeight: 1.2 }}>{g.name}</h3>
        <p style={{ fontSize: 14, color: COLORS.soft, margin: 0, lineHeight: 1.5 }}>{g.desc}</p>
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginTop: 18, paddingTop: 16, borderTop: "1px solid " + COLORS.line }}>
          <span style={{ fontWeight: 600, fontSize: 18 }}>R$ {g.price.toLocaleString("pt-BR")}</span>
          {!isOut && (
            <button onClick={onReserve} style={{
              background: COLORS.ink, color: "#fff", border: "none",
              padding: "10px 18px", borderRadius: 999, fontSize: 13, fontWeight: 600,
              cursor: "pointer", fontFamily: "inherit", display: "inline-flex", alignItems: "center", gap: 6,
              transition: "background 200ms ease, transform 200ms ease",
            }}
            onMouseEnter={(e) => { e.currentTarget.style.background = g.accent; }}
            onMouseLeave={(e) => { e.currentTarget.style.background = COLORS.ink; }}
            >Quero esse <span>→</span></button>
          )}
        </div>
      </div>
    </article>
  );
}

/* ===== GIFTS ===== */
function Gifts({ cats, cat, setCat, list, onReserve }) {
  return (
    <main style={{ padding: "56px" }}>
      <div style={{ marginBottom: 40 }}>
        <div style={{ fontSize: 12, fontWeight: 600, letterSpacing: "0.18em", color: COLORS.mute, textTransform: "uppercase", marginBottom: 14 }}>A lista inteira · 8 itens</div>
        <h1 style={{ fontWeight: 700, fontSize: 96, letterSpacing: "-0.05em", lineHeight: 0.92, margin: 0 }}>
          Tudo que tá <span style={{ color: COLORS.purple, fontStyle: "italic", fontWeight: 700 }}>na lista.</span>
        </h1>
      </div>

      <div style={{ display: "flex", flexWrap: "wrap", gap: 6, padding: "16px 0", borderTop: "1px solid " + COLORS.line, borderBottom: "1px solid " + COLORS.line, marginBottom: 32, alignItems: "center" }}>
        {cats.map((c) => (
          <button key={c} onClick={() => setCat(c)} style={{
            padding: "8px 14px", fontSize: 13, fontWeight: 500,
            background: cat === c ? COLORS.ink : "transparent",
            color: cat === c ? COLORS.bg : COLORS.soft,
            border: "1px solid " + (cat === c ? COLORS.ink : "rgba(15,15,18,0.12)"),
            borderRadius: 999, cursor: "pointer", fontFamily: "inherit", transition: "all 220ms ease",
          }}>{c}</button>
        ))}
        <span style={{ marginLeft: "auto", alignSelf: "center", fontSize: 12, color: COLORS.mute }}>{list.length} {list.length === 1 ? "item" : "itens"}</span>
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 20 }}>
        {list.map((g, i) => <Card key={g.id} g={g} delay={i * 70} onReserve={() => onReserve(g)} />)}
      </div>
    </main>
  );
}

/* ===== PIX ===== */
function Pix({ amount, setAmount, copied, onCopy }) {
  const presets = [50, 100, 150, 250, 500, 1000];
  return (
    <main style={{ padding: 56 }}>
      <div style={{ marginBottom: 40 }}>
        <div style={{ fontSize: 12, fontWeight: 600, letterSpacing: "0.18em", color: COLORS.mute, textTransform: "uppercase", marginBottom: 14 }}>Mandar pelo Pix</div>
        <h1 style={{ fontWeight: 700, fontSize: 96, letterSpacing: "-0.05em", lineHeight: 0.92, margin: 0 }}>
          Sem cadastro,<br />sem <span style={{ color: COLORS.purple, fontStyle: "italic", fontWeight: 700 }}>pressa.</span>
        </h1>
        <p style={{ fontSize: 16, color: COLORS.soft, marginTop: 18, maxWidth: 560 }}>
          Se preferir mandar um valor — qualquer valor — eu somo com as outras contribuições e cuido dos itens da lista. Funciona igualzinho um Pix normal.
        </p>
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 20 }}>
        <div style={{ background: COLORS.paper, border: "1px solid " + COLORS.line, padding: 32, borderRadius: 6 }}>
          <div style={{ fontSize: 12, fontWeight: 600, letterSpacing: "0.18em", color: COLORS.mute, textTransform: "uppercase", marginBottom: 24 }}>Dados do Pix</div>
          <div style={{ display: "grid", gridTemplateColumns: "1fr 200px", gap: 24 }}>
            <div>
              {[["Quem recebe", "Rafael S. Andrade"], ["Chave (e-mail)", "rafael.aniv@email.com"], ["Banco", "260 — Nu Pagamentos"], ["Cidade", "São Paulo — SP"]].map(([k, v]) => (
                <div key={k} style={{ marginBottom: 18 }}>
                  <div style={{ fontSize: 11, fontWeight: 600, letterSpacing: "0.16em", color: COLORS.mute, textTransform: "uppercase", marginBottom: 4 }}>{k}</div>
                  <div style={{ fontSize: 15, color: COLORS.ink, fontWeight: 500 }}>{v}</div>
                </div>
              ))}
            </div>
            <div style={{ background: "#fff", border: "1px solid " + COLORS.line, padding: 8, aspectRatio: "1", display: "grid", gridTemplateColumns: "repeat(14, 1fr)", gridTemplateRows: "repeat(14, 1fr)", gap: 1, position: "relative", overflow: "hidden" }}>
              {Array.from({ length: 196 }).map((_, i) => {
                const on = (i * 37 + (i % 11) * 7) % 5 < 2;
                const r = i % 14, c = Math.floor(i / 14);
                const corner = (r < 3 && c < 3) || (r >= 11 && c < 3) || (r < 3 && c >= 11);
                return <div key={i} style={{ background: corner || on ? COLORS.ink : "transparent" }}></div>;
              })}
              {/* scanning line */}
              <div aria-hidden style={{ position: "absolute", left: 0, right: 0, top: 0, height: 2, background: COLORS.purple, animation: "qrScan 3s linear infinite", opacity: 0.6 }}></div>
            </div>
          </div>
          <div style={{ marginTop: 24, paddingTop: 20, borderTop: "1px solid " + COLORS.line }}>
            <div style={{ fontSize: 11, fontWeight: 600, letterSpacing: "0.16em", color: COLORS.mute, textTransform: "uppercase", marginBottom: 10 }}>Pix copia e cola</div>
            <div style={{
              fontFamily: "'JetBrains Mono', monospace", fontSize: 12, lineHeight: 1.5,
              background: COLORS.bg, border: "1px solid " + COLORS.line,
              padding: 14, color: COLORS.ink, wordBreak: "break-all", borderRadius: 4,
            }}>00020126360014BR.GOV.BCB.PIX0114rafael.aniv@email5204000053039865406150.005802BR5919RAFAEL S ANDRADE6009SAO PAULO62070503***6304B7C9</div>
            <button onClick={onCopy} style={{
              marginTop: 14, width: "100%",
              background: copied ? COLORS.purple : COLORS.ink, color: "#fff",
              border: "none", padding: "14px", fontSize: 14, fontWeight: 600,
              cursor: "pointer", borderRadius: 4, fontFamily: "inherit",
              transition: "background 220ms ease",
            }}>{copied ? "✓ Copiado, é só colar no app" : "Copiar código do Pix"}</button>
          </div>
        </div>

        <div style={{ background: COLORS.paper, border: "1px solid " + COLORS.line, padding: 32, borderRadius: 6, position: "relative", overflow: "hidden" }}>
          <div aria-hidden style={{
            position: "absolute", right: -80, top: -80, width: 240, height: 240, borderRadius: "50%",
            background: "radial-gradient(circle, " + COLORS.purpleSoft + ", transparent 70%)", filter: "blur(10px)",
          }}></div>
          <div style={{ position: "relative" }}>
            <div style={{ display: "inline-flex", alignItems: "center", gap: 8, padding: "6px 12px", background: COLORS.purpleSoft, borderRadius: 999, marginBottom: 22 }}>
              <span style={{ width: 6, height: 6, background: COLORS.purple, borderRadius: 999, animation: "pulse 2s ease-in-out infinite" }}></span>
              <span style={{ fontSize: 12, color: COLORS.purple, fontWeight: 600 }}>Vai pro: Fone HD 660S2</span>
            </div>

            <div style={{ fontSize: 12, fontWeight: 600, letterSpacing: "0.16em", color: COLORS.mute, textTransform: "uppercase", marginBottom: 12 }}>Quanto você quer mandar</div>
            <div style={{ display: "flex", alignItems: "baseline", gap: 12, paddingBottom: 16, borderBottom: "2px solid " + COLORS.ink, transition: "border-color 200ms ease" }}>
              <span style={{ fontWeight: 500, fontSize: 28, color: COLORS.mute }}>R$</span>
              <input type="number" value={amount} onChange={(e) => setAmount(Number(e.target.value) || 0)} style={{
                flex: 1, background: "transparent", border: "none", outline: "none",
                fontWeight: 700, fontSize: 88, letterSpacing: "-0.04em", color: COLORS.ink, padding: 0, lineHeight: 1, fontFamily: "inherit",
              }} />
            </div>
            <div style={{ display: "flex", flexWrap: "wrap", gap: 8, marginTop: 18 }}>
              {presets.map((v) => (
                <button key={v} onClick={() => setAmount(v)} style={{
                  padding: "8px 16px", fontSize: 13, fontWeight: 500,
                  background: amount === v ? COLORS.ink : "transparent",
                  color: amount === v ? "#fff" : COLORS.soft,
                  border: "1px solid " + (amount === v ? COLORS.ink : "rgba(15,15,18,0.12)"),
                  borderRadius: 999, cursor: "pointer", fontFamily: "inherit", transition: "all 200ms ease",
                }}>R$ {v}</button>
              ))}
            </div>

            <div style={{ marginTop: 28, display: "grid", gap: 18 }}>
              <Field label="Seu nome" placeholder="Como você assina embaixo" />
              <Field label="E-mail (pra eu te agradecer)" placeholder="voce@exemplo.com" />
              <Field label="Quer deixar um recado?" placeholder="Parabéns, mano…" textarea />
            </div>

            <button style={{
              marginTop: 28, width: "100%", background: COLORS.ink, color: "#fff",
              border: "none", padding: "16px", fontSize: 15, fontWeight: 600,
              cursor: "pointer", borderRadius: 4, fontFamily: "inherit",
              transition: "background 200ms ease",
            }}
            onMouseEnter={(e) => { e.currentTarget.style.background = COLORS.purple; }}
            onMouseLeave={(e) => { e.currentTarget.style.background = COLORS.ink; }}
            >Confirmar e mandar →</button>
          </div>
        </div>
      </div>
    </main>
  );
}

function Field({ label, placeholder, textarea = false }) {
  const [focus, setFocus] = React.useState(false);
  const Cmp = textarea ? "textarea" : "input";
  return (
    <div>
      <label style={{ fontSize: 11, fontWeight: 600, letterSpacing: "0.16em", color: focus ? COLORS.purple : COLORS.mute, textTransform: "uppercase", transition: "color 200ms ease" }}>{label}</label>
      <Cmp rows={textarea ? 2 : undefined} placeholder={placeholder}
        onFocus={() => setFocus(true)} onBlur={() => setFocus(false)}
        style={{
          width: "100%", marginTop: 6, padding: "10px 0",
          background: "transparent", border: "none",
          borderBottom: "1px solid " + (focus ? COLORS.purple : "rgba(15,15,18,0.12)"),
          fontFamily: "inherit", fontSize: 15, color: COLORS.ink, outline: "none", resize: "none",
          transition: "border-color 200ms ease",
        }} />
    </div>
  );
}

/* ===== MY ===== */
function My() {
  const data = [
    { ...GIFTS_V2[0], status: "reserved", date: "12/04/2026 · 19:42" },
    { ...GIFTS_V2[5], status: "confirmed", date: "08/04/2026 · 11:10" },
  ];
  return (
    <main style={{ padding: 56 }}>
      <div style={{ marginBottom: 40 }}>
        <div style={{ fontSize: 12, fontWeight: 600, letterSpacing: "0.18em", color: COLORS.mute, textTransform: "uppercase", marginBottom: 14 }}>Suas reservas</div>
        <h1 style={{ fontWeight: 700, fontSize: 96, letterSpacing: "-0.05em", lineHeight: 0.92, margin: 0 }}>
          O que você <span style={{ color: COLORS.purple, fontStyle: "italic", fontWeight: 700 }}>já garantiu.</span>
        </h1>
      </div>
      <div style={{ display: "grid", gap: 16 }}>
        {data.map((r, i) => (
          <article key={r.id} style={{
            background: COLORS.paper, border: "1px solid " + COLORS.line, borderRadius: 6,
            display: "grid", gridTemplateColumns: "200px 1fr auto", overflow: "hidden",
            animation: `fadeUp 600ms ${i * 100}ms both cubic-bezier(0.2,0.8,0.2,1)`,
            transition: "transform 240ms ease, box-shadow 240ms ease",
          }}
          onMouseEnter={(e) => { e.currentTarget.style.transform = "translateX(4px)"; e.currentTarget.style.boxShadow = `0 12px 32px -10px rgba(15,15,18,0.15)`; }}
          onMouseLeave={(e) => { e.currentTarget.style.transform = ""; e.currentTarget.style.boxShadow = ""; }}
          >
            <div style={{ background: r.color, height: 200, position: "relative", overflow: "hidden" }}>
              <div aria-hidden style={{ position: "absolute", inset: -30, background: `radial-gradient(circle at 30% 30%, ${r.accent}66, transparent 60%)`, filter: "blur(20px)" }}></div>
              <div style={{ position: "absolute", inset: 0, display: "flex", alignItems: "center", justifyContent: "center", fontWeight: 700, fontSize: 100, color: "rgba(255,255,255,0.92)", letterSpacing: "-0.06em" }}>{r.name.split(" ").pop()[0]}</div>
            </div>
            <div style={{ padding: 24 }}>
              <div style={{ fontSize: 11, fontWeight: 600, letterSpacing: "0.16em", color: COLORS.mute, textTransform: "uppercase", marginBottom: 8 }}>
                n° {String(r.id).padStart(2, "0")} · {r.cat} · reservado em {r.date}
              </div>
              <h3 style={{ fontWeight: 600, fontSize: 24, letterSpacing: "-0.02em", margin: "0 0 6px" }}>{r.name}</h3>
              <p style={{ fontSize: 14, color: COLORS.soft, margin: 0, maxWidth: 480, lineHeight: 1.5 }}>{r.desc}</p>
              <div style={{
                marginTop: 14, display: "inline-flex", alignItems: "center", gap: 8, padding: "5px 12px",
                background: r.status === "reserved" ? COLORS.purpleSoft : "#EAF4EE",
                borderRadius: 999, fontSize: 12, fontWeight: 600,
                color: r.status === "reserved" ? COLORS.purple : "#1d6e3a",
              }}>
                <span style={{ width: 6, height: 6, background: r.status === "reserved" ? COLORS.purple : "#1d6e3a", borderRadius: 999, animation: r.status === "reserved" ? "pulse 2s ease-in-out infinite" : "none" }}></span>
                {r.status === "reserved" ? "Reservado · você tem 4 dias e 12h pra confirmar" : "Já entreguei · obrigado!"}
              </div>
            </div>
            <div style={{ padding: 24, display: "flex", flexDirection: "column", justifyContent: "center", alignItems: "flex-end", gap: 12 }}>
              <span style={{ fontWeight: 600, fontSize: 22 }}>R$ {r.price.toLocaleString("pt-BR")}</span>
              {r.status === "reserved" ? (
                <div style={{ display: "flex", gap: 8 }}>
                  <button style={btn.ghostSm}>Cancelar</button>
                  <button style={btn.solidSm}>Pagar com Pix →</button>
                </div>
              ) : <span style={{ fontSize: 13, color: COLORS.mute }}>Concluído</span>}
            </div>
          </article>
        ))}
      </div>
    </main>
  );
}

/* ===== AUTH ===== */
function Auth({ tab, setTab, gift, onClose }) {
  return (
    <div style={{
      position: "fixed", inset: 0, zIndex: 200,
      background: "rgba(15,15,18,0.42)", backdropFilter: "blur(8px)",
      display: "flex", alignItems: "center", justifyContent: "center",
      animation: "fade 240ms ease",
    }} onClick={onClose}>
      <div onClick={(e) => e.stopPropagation()} style={{
        width: 720, background: COLORS.paper, borderRadius: 6,
        boxShadow: "0 60px 120px -20px rgba(15,15,18,0.36)",
        display: "grid", gridTemplateColumns: "260px 1fr", overflow: "hidden",
        animation: "slideUpScale 460ms cubic-bezier(0.16,1,0.3,1)",
      }}>
        <div style={{
          background: "linear-gradient(160deg, " + gift.accent + " 0%, " + COLORS.ink + " 110%)",
          color: "#fff", padding: 28, position: "relative", overflow: "hidden",
        }}>
          <div aria-hidden style={{
            position: "absolute", inset: 0,
            backgroundImage: "radial-gradient(rgba(255,255,255,0.08) 1px, transparent 1px)",
            backgroundSize: "4px 4px",
          }}></div>
          <div aria-hidden style={{
            position: "absolute", right: -40, bottom: -40, width: 200, height: 200, borderRadius: "50%",
            background: "rgba(255,255,255,0.08)", animation: "float1 10s ease-in-out infinite",
          }}></div>
          <div style={{ position: "relative" }}>
            <div style={{ fontSize: 11, fontWeight: 600, letterSpacing: "0.16em", textTransform: "uppercase", color: "rgba(255,255,255,0.7)", marginBottom: 14 }}>Reservando</div>
            <div style={{ fontWeight: 700, fontSize: 28, letterSpacing: "-0.025em", lineHeight: 1.05 }}>{gift.name}.</div>
            <div style={{ fontSize: 13, color: "rgba(255,255,255,0.7)", marginTop: 12 }}>n° {String(gift.id).padStart(2, "0")} · {gift.cat} · R$ {gift.price.toLocaleString("pt-BR")}</div>
            <div style={{ position: "absolute", left: 0, right: 0, bottom: -12, fontSize: 12, color: "rgba(255,255,255,0.7)", lineHeight: 1.5, paddingTop: 28 }}>
              Pra reservar a gente só precisa do seu e-mail. Você tem uma semana pra confirmar com calma.
            </div>
          </div>
        </div>
        <div style={{ padding: 32 }}>
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 20 }}>
            <h2 style={{ fontWeight: 700, fontSize: 26, letterSpacing: "-0.02em", margin: 0 }}>
              {tab === "signup" ? "Criar conta" : "Já tenho conta"}
            </h2>
            <button onClick={onClose} style={{ background: "transparent", border: "1px solid rgba(15,15,18,0.12)", color: COLORS.soft, width: 32, height: 32, cursor: "pointer", fontFamily: "inherit", fontSize: 16, borderRadius: 4 }}>×</button>
          </div>
          <div style={{ display: "inline-flex", padding: 4, background: COLORS.bg, border: "1px solid " + COLORS.line, borderRadius: 999, marginBottom: 22, position: "relative" }}>
            <span style={{
              position: "absolute", top: 4, bottom: 4, left: tab === "signup" ? 4 : "calc(50% + 0px)",
              width: "calc(50% - 4px)", background: COLORS.ink, borderRadius: 999,
              transition: "left 280ms cubic-bezier(0.2,0.8,0.2,1)",
            }}></span>
            {[["signup", "Criar"], ["login", "Já tenho"]].map(([k, l]) => (
              <button key={k} onClick={() => setTab(k)} style={{
                position: "relative", zIndex: 2,
                padding: "8px 22px", fontSize: 13, fontWeight: 600,
                background: "transparent",
                color: tab === k ? "#fff" : COLORS.soft,
                border: "none", borderRadius: 999, cursor: "pointer", fontFamily: "inherit",
                transition: "color 280ms ease",
              }}>{l}</button>
            ))}
          </div>
          <div style={{ display: "grid", gap: 16 }}>
            {tab === "signup" && <Field label="Como você se chama" placeholder="Seu nome" />}
            <Field label="E-mail" placeholder="voce@exemplo.com" />
            <Field label="Senha" placeholder="·······" />
            <button style={{
              marginTop: 8, background: COLORS.ink, color: "#fff",
              border: "none", padding: "14px", fontSize: 14, fontWeight: 600,
              cursor: "pointer", borderRadius: 4, fontFamily: "inherit",
              transition: "background 200ms ease",
            }}
            onMouseEnter={(e) => { e.currentTarget.style.background = gift.accent; }}
            onMouseLeave={(e) => { e.currentTarget.style.background = COLORS.ink; }}
            >{tab === "signup" ? "Criar conta e reservar →" : "Entrar e reservar →"}</button>
          </div>
        </div>
      </div>
    </div>
  );
}

/* ===== FOOTER ===== */
function Footer() {
  return (
    <footer style={{ borderTop: "1px solid " + COLORS.line, padding: "40px 56px 56px", background: COLORS.paper }}>
      <div style={{ display: "grid", gridTemplateColumns: "2fr 1fr 1fr 1fr", gap: 32 }}>
        <div>
          <div style={{ fontWeight: 700, fontSize: 22, letterSpacing: "-0.02em", marginBottom: 8 }}>Rafael, 30.</div>
          <div style={{ fontSize: 13, color: COLORS.mute, maxWidth: 360, lineHeight: 1.5 }}>Obrigado por chegar até aqui. Qualquer dúvida, manda mensagem direto pra mim.</div>
        </div>
        {[
          ["Mapa", ["Início", "Lista", "Pix", "Minhas reservas"]],
          ["Falar comigo", ["WhatsApp", "Instagram"]],
          ["Sobre", ["Como funciona", "Privacidade"]],
        ].map(([t, items]) => (
          <div key={t}>
            <div style={{ fontSize: 11, fontWeight: 600, letterSpacing: "0.16em", color: COLORS.mute, textTransform: "uppercase", marginBottom: 12 }}>{t}</div>
            {items.map((i) => <div key={i} style={{ fontSize: 14, color: COLORS.ink, marginBottom: 6, cursor: "pointer" }}>{i}</div>)}
          </div>
        ))}
      </div>
    </footer>
  );
}

const btn = {
  solid:    { background: COLORS.ink, color: COLORS.bg, border: "none", padding: "16px 26px", fontSize: 14, fontWeight: 600, borderRadius: 999, cursor: "pointer", fontFamily: "inherit", display: "inline-flex", alignItems: "center" },
  ghost:    { background: "transparent", color: COLORS.ink, border: "1px solid rgba(15,15,18,0.18)", padding: "16px 26px", fontSize: 14, fontWeight: 600, borderRadius: 999, cursor: "pointer", fontFamily: "inherit" },
  solidSm:  { background: COLORS.ink, color: "#fff", border: "none", padding: "8px 16px", fontSize: 13, fontWeight: 600, borderRadius: 999, cursor: "pointer", fontFamily: "inherit" },
  ghostSm:  { background: "transparent", color: COLORS.soft, border: "1px solid rgba(15,15,18,0.12)", padding: "8px 14px", fontSize: 13, fontWeight: 500, borderRadius: 999, cursor: "pointer", fontFamily: "inherit" },
};

function Anim() {
  return <style>{`
    @keyframes pulse { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.6); opacity: 0.4; } }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    @keyframes fade { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUpScale { from { opacity: 0; transform: translateY(20px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }
    @keyframes pageIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes drawSquiggle { to { stroke-dashoffset: 0; } }
    @keyframes float1 { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(40px, 30px); } }
    @keyframes float2 { 0%, 100% { transform: translate(0, 0) scale(1); } 50% { transform: translate(-30px, 40px) scale(1.1); } }
    @keyframes floatCard { 0%, 100% { transform: translateY(0) rotate(var(--r, 0deg)); } 50% { transform: translateY(-12px) rotate(var(--r, 0deg)); } }
    @keyframes marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }
    @keyframes slideUnderline { from { transform: scaleX(0); transform-origin: left; } to { transform: scaleX(1); } }
    @keyframes qrScan { 0% { transform: translateY(0); } 100% { transform: translateY(180px); } }
    .btn-magnetic:hover .btn-arrow { transform: translateX(4px); }
    @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: 0.001ms !important; transition-duration: 0.001ms !important; } }
    input::placeholder, textarea::placeholder { color: #a8a8b0; }
    input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }
    ::selection { background: ${COLORS.purple}; color: #fff; }
  `}</style>;
}

window.App = App;
