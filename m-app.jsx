/* MASCULINE — Home, Gifts, Pix combined into a single full-bleed prototype with anchor nav */

function MasculineApp() {
  const [route, setRoute] = React.useState("home");
  const [filter, setFilter] = React.useState("ALL");
  const [hoverId, setHoverId] = React.useState(null);
  const [pixCopied, setPixCopied] = React.useState(false);
  const [authOpen, setAuthOpen] = React.useState(false);
  const [authTab, setAuthTab] = React.useState("signup");
  const [scrambleKey, setScrambleKey] = React.useState(0);
  const [pixAmount, setPixAmount] = React.useState(150);

  // live countdown
  const target = React.useMemo(() => new Date(Date.now() + (47 * 86400 + 8 * 3600 + 23 * 60) * 1000), []);
  const [now, setNow] = React.useState(Date.now());
  React.useEffect(() => {
    const t = setInterval(() => setNow(Date.now()), 1000);
    return () => clearInterval(t);
  }, []);
  const diff = Math.max(0, target.getTime() - now);
  const dd = Math.floor(diff / 86400000);
  const hh = Math.floor((diff % 86400000) / 3600000);
  const mm = Math.floor((diff % 3600000) / 60000);
  const ss = Math.floor((diff % 60000) / 1000);

  // re-trigger scramble on route change
  React.useEffect(() => { setScrambleKey((k) => k + 1); }, [route]);

  const cats = ["ALL", "AUDIO", "GEAR", "BAR", "TIME", "OFFICE", "PHOTO", "EDC", "DESK"];
  const visibleGifts = filter === "ALL" ? SAMPLE_GIFTS_M : SAMPLE_GIFTS_M.filter((g) => g.category === filter);

  return (
    <div style={{
      width: "100%", minHeight: "100vh", background: "#0a0a0d",
      color: "#e8e8ee", fontFamily: "'Inter', system-ui, sans-serif",
      position: "relative", overflow: "hidden",
    }}>
      {/* GLOBAL bg grid + noise */}
      <div style={{
        position: "fixed", inset: 0,
        backgroundImage: "linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px)",
        backgroundSize: "48px 48px",
        pointerEvents: "none", zIndex: 0,
      }}></div>
      <div style={{
        position: "fixed", inset: 0,
        backgroundImage: "radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px)",
        backgroundSize: "3px 3px",
        opacity: 0.18, mixBlendMode: "overlay",
        pointerEvents: "none", zIndex: 0,
      }}></div>
      {/* aurora orbs */}
      <div style={{
        position: "fixed", left: "-10%", top: "-10%",
        width: 600, height: 600, borderRadius: 999,
        background: "radial-gradient(circle, rgba(0,200,255,0.18), transparent 60%)",
        filter: "blur(40px)", animation: "auroraM 18s ease-in-out infinite",
        pointerEvents: "none", zIndex: 0,
      }}></div>
      <div style={{
        position: "fixed", right: "-15%", bottom: "-15%",
        width: 700, height: 700, borderRadius: 999,
        background: "radial-gradient(circle, rgba(0,255,180,0.14), transparent 65%)",
        filter: "blur(50px)", animation: "auroraM 22s ease-in-out -6s infinite",
        pointerEvents: "none", zIndex: 0,
      }}></div>

      {/* HEADER */}
      <header style={{
        position: "sticky", top: 0, zIndex: 100,
        background: "rgba(10,10,13,0.72)", backdropFilter: "blur(24px)",
        borderBottom: "1px solid rgba(255,255,255,0.06)",
        padding: "16px 56px", display: "grid", gridTemplateColumns: "1fr auto 1fr", alignItems: "center",
      }}>
        <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 11, letterSpacing: "0.2em", color: "#7a7a8a" }}>
          [SYS] · uptime <span style={{ color: "#00FFB4" }}>●</span> connected
        </div>
        <div style={{
          fontFamily: "'JetBrains Mono', monospace", fontWeight: 500,
          fontSize: 14, letterSpacing: "0.04em",
        }}>
          <span style={{ color: "#7a7a8a" }}>$</span>{" "}
          rafael<span style={{ color: "#00FFB4" }}>@</span>30.run
        </div>
        <nav style={{ display: "flex", gap: 4, justifyContent: "flex-end" }}>
          {[["home","00 / home"], ["gifts","01 / lista"], ["pix","02 / pix"], ["my","03 / meus"]].map(([k, l]) => (
            <button key={k} onClick={() => setRoute(k)} style={{
              padding: "8px 14px", fontFamily: "'JetBrains Mono', monospace",
              fontSize: 11, letterSpacing: "0.14em", textTransform: "uppercase",
              background: route === k ? "#fff" : "transparent",
              color: route === k ? "#0a0a0d" : "#a3a3b0",
              border: "1px solid " + (route === k ? "#fff" : "rgba(255,255,255,0.12)"),
              cursor: "pointer", borderRadius: 2, transition: "all 200ms ease",
            }}>{l}</button>
          ))}
        </nav>
      </header>

      <div style={{ position: "relative", zIndex: 1 }}>
        {route === "home" && <HomeM dd={dd} hh={hh} mm={mm} ss={ss} key={scrambleKey} setRoute={setRoute} setAuthOpen={setAuthOpen} />}
        {route === "gifts" && (
          <GiftsM
            cats={cats} filter={filter} setFilter={setFilter}
            gifts={visibleGifts} hoverId={hoverId} setHoverId={setHoverId}
            onReserve={() => setAuthOpen(true)}
          />
        )}
        {route === "pix" && (
          <PixM amount={pixAmount} setAmount={setPixAmount} copied={pixCopied}
            onCopy={() => { setPixCopied(true); setTimeout(() => setPixCopied(false), 1500); }} />
        )}
        {route === "my" && <MyM />}
      </div>

      {/* AUTH MODAL */}
      {authOpen && <AuthM tab={authTab} setTab={setAuthTab} onClose={() => setAuthOpen(false)} />}

      <Animations />
    </div>
  );
}

/* ============ HOME ============ */
function HomeM({ dd, hh, mm, ss, setRoute, setAuthOpen }) {
  return (
    <main>
      {/* HERO */}
      <section style={{ padding: "72px 56px 24px" }}>
        <div style={{ display: "grid", gridTemplateColumns: "1.4fr 1fr", gap: 56, alignItems: "stretch" }}>
          <div>
            <MTag style={{ marginBottom: 22 }}>
              <span style={{ width: 6, height: 6, background: "#00FFB4", borderRadius: 999 }}></span>
              [exec] event in T-{dd}d
            </MTag>
            <h1 style={{
              fontFamily: "'Inter', sans-serif", fontWeight: 700,
              fontSize: 156, letterSpacing: "-0.07em", lineHeight: 0.85,
              margin: 0, color: "#fff",
            }}>
              <span style={{ display: "block" }}><ScrambleText>RAFAEL</ScrambleText></span>
              <span style={{ display: "block", color: "#7a7a8a" }}>
                _<span style={{ color: "#00FFB4" }}>30</span>.run
              </span>
            </h1>
            <p style={{ fontSize: 17, color: "#a3a3b0", marginTop: 32, maxWidth: 520, lineHeight: 1.6 }}>
              Não é uma lista de aniversário, é um <b style={{ color: "#fff" }}>release plan</b>. 
              8 itens, prazo definido, status público. Reserva direta ou Pix para
              os que preferem participar do funding.
            </p>
            <div style={{ display: "flex", gap: 12, marginTop: 36 }}>
              <button onClick={() => setRoute("gifts")} style={btnSolidM}>
                <span>./browse_gifts</span>
                <span style={{ color: "#00FFB4" }}>→</span>
              </button>
              <button onClick={() => setRoute("pix")} style={btnGhostM}>./contribute_pix</button>
            </div>

            {/* stats strip */}
            <div style={{ marginTop: 56, display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 0, borderTop: "1px solid rgba(255,255,255,0.08)" }}>
              {[
                ["08", "items"],
                ["02", "reserved"],
                ["03", "contribs"],
                ["47%", "funded"],
              ].map(([v, l], i) => (
                <div key={l} style={{
                  padding: "20px 0", borderRight: i < 3 ? "1px solid rgba(255,255,255,0.08)" : "none",
                  paddingLeft: i > 0 ? 20 : 0,
                }}>
                  <div style={{
                    fontFamily: "'JetBrains Mono', monospace", fontWeight: 500,
                    fontSize: 36, color: "#fff", letterSpacing: "-0.03em",
                  }}>{v}</div>
                  <div style={{
                    fontFamily: "'JetBrains Mono', monospace", fontSize: 10,
                    letterSpacing: "0.2em", textTransform: "uppercase", color: "#7a7a8a", marginTop: 4,
                  }}>{l}</div>
                </div>
              ))}
            </div>
          </div>

          {/* COUNTDOWN PANEL */}
          <div style={{
            background: "linear-gradient(180deg, #0f0f14 0%, #07070a 100%)",
            border: "1px solid rgba(255,255,255,0.08)", padding: 32, position: "relative", overflow: "hidden",
          }}>
            <div style={{ display: "flex", justifyContent: "space-between", marginBottom: 22 }}>
              <MTag kind="cyan">● live · countdown</MTag>
              <span style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.18em", color: "#7a7a8a" }}>
                EVT-12.06.2026
              </span>
            </div>

            {/* big day digit */}
            <div style={{ position: "relative" }}>
              <div style={{
                fontFamily: "'JetBrains Mono', monospace", fontWeight: 500,
                fontSize: 220, lineHeight: 0.85, letterSpacing: "-0.06em",
                color: "#fff", textShadow: "0 0 60px rgba(0,200,255,0.35)",
              }}>{String(dd).padStart(2, "0")}</div>
              <div style={{
                position: "absolute", top: 12, right: 0,
                fontFamily: "'JetBrains Mono', monospace", fontSize: 11,
                letterSpacing: "0.18em", color: "#00C8FF",
              }}>::DAYS</div>
            </div>

            {/* h/m/s with animated bars */}
            <div style={{ marginTop: 20, display: "grid", gridTemplateColumns: "1fr 1fr 1fr", gap: 14 }}>
              {[["hh", hh, 24], ["mm", mm, 60], ["ss", ss, 60]].map(([l, v, max]) => (
                <div key={l}>
                  <div style={{
                    fontFamily: "'JetBrains Mono', monospace", fontWeight: 500,
                    fontSize: 32, color: "#fff", letterSpacing: "-0.02em",
                  }}>{String(v).padStart(2, "0")}</div>
                  <div style={{
                    fontFamily: "'JetBrains Mono', monospace", fontSize: 9,
                    letterSpacing: "0.2em", color: "#7a7a8a", textTransform: "uppercase", marginBottom: 6,
                  }}>{l}</div>
                  <div style={{ height: 2, background: "rgba(255,255,255,0.08)", position: "relative" }}>
                    <div style={{
                      position: "absolute", left: 0, top: 0, bottom: 0,
                      width: `${(v / max) * 100}%`,
                      background: "linear-gradient(90deg, #00C8FF, #00FFB4)",
                      transition: "width 400ms ease",
                    }}></div>
                  </div>
                </div>
              ))}
            </div>

            {/* progress meter */}
            <div style={{ marginTop: 32, paddingTop: 22, borderTop: "1px solid rgba(255,255,255,0.08)" }}>
              <div style={{ display: "flex", justifyContent: "space-between", marginBottom: 8 }}>
                <span style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.18em", color: "#7a7a8a", textTransform: "uppercase" }}>Funding progress</span>
                <span style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 11, color: "#00FFB4" }}>47.2%</span>
              </div>
              <div style={{ height: 8, background: "rgba(255,255,255,0.06)", border: "1px solid rgba(255,255,255,0.08)", position: "relative" }}>
                <div style={{
                  position: "absolute", left: 0, top: 0, bottom: 0, width: "47.2%",
                  background: "linear-gradient(90deg, #00C8FF, #00FFB4)",
                  boxShadow: "0 0 16px rgba(0,255,180,0.5)",
                  animation: "progressFlow 2s ease-in-out infinite",
                }}></div>
              </div>
              <div style={{ display: "flex", justifyContent: "space-between", marginTop: 8, fontFamily: "'JetBrains Mono', monospace", fontSize: 10, color: "#7a7a8a" }}>
                <span>R$ 13.100</span><span>de R$ 27.770</span>
              </div>
            </div>

            {/* glow accents */}
            <div style={{
              position: "absolute", right: -100, top: -100, width: 280, height: 280, borderRadius: 999,
              background: "radial-gradient(circle, rgba(0,200,255,0.4), transparent 60%)",
              filter: "blur(40px)", pointerEvents: "none",
              animation: "pulseM 4s ease-in-out infinite",
            }}></div>
          </div>
        </div>
      </section>

      {/* TICKER */}
      <div style={{
        marginTop: 24, padding: "18px 0",
        borderTop: "1px solid rgba(255,255,255,0.08)",
        borderBottom: "1px solid rgba(255,255,255,0.08)",
        overflow: "hidden", whiteSpace: "nowrap",
        background: "rgba(0,0,0,0.4)",
      }}>
        <div style={{ display: "inline-block", animation: "tickerM 32s linear infinite", fontFamily: "'JetBrains Mono', monospace", fontSize: 14, letterSpacing: "0.04em" }}>
          {Array.from({ length: 4 }).map((_, i) => (
            <span key={i} style={{ marginRight: 56, color: "#a3a3b0" }}>
              <span style={{ color: "#00FFB4" }}>▲</span> sennheiser hd 660s2
              <span style={{ color: "#7a7a8a", margin: "0 18px" }}>////</span>
              <span style={{ color: "#00C8FF" }}>▲</span> ricoh gr iii x
              <span style={{ color: "#7a7a8a", margin: "0 18px" }}>////</span>
              <span style={{ color: "#00FFB4" }}>▲</span> yamazaki 12y
              <span style={{ color: "#7a7a8a", margin: "0 18px" }}>////</span>
              <span style={{ color: "#00C8FF" }}>▲</span> herman miller aeron
              <span style={{ color: "#7a7a8a", margin: "0 18px" }}>////</span>
              <span style={{ color: "#00FFB4" }}>▲</span> salomon xt-6
              <span style={{ color: "#7a7a8a", margin: "0 18px" }}>////</span>
            </span>
          ))}
        </div>
      </div>

      {/* FEATURED grid */}
      <section style={{ padding: "72px 56px" }}>
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-end", marginBottom: 32 }}>
          <div>
            <MTag style={{ marginBottom: 14 }}>// 01_featured</MTag>
            <h2 style={{
              fontFamily: "'Inter', sans-serif", fontWeight: 700,
              fontSize: 64, margin: 0, letterSpacing: "-0.045em", color: "#fff",
            }}>
              prioritários<span style={{ color: "#00FFB4" }}>.</span>
            </h2>
          </div>
          <button onClick={() => setRoute("gifts")} style={btnGhostM}>view all 08 →</button>
        </div>

        <div style={{ display: "grid", gridTemplateColumns: "1.4fr 1fr 1fr", gap: 16 }}>
          {SAMPLE_GIFTS_M.slice(0, 3).map((g, i) => {
            const hue = [192, 158, 280][i];
            return (
              <article key={g.id} style={{
                background: "#0f0f14", border: "1px solid rgba(255,255,255,0.08)",
                padding: 16, position: "relative",
              }}>
                <GiftCubeM label={g.name[0]} hue={hue} />
                <div style={{ display: "flex", justifyContent: "space-between", marginTop: 16, fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.18em", color: "#7a7a8a" }}>
                  <span>ID-{String(g.id).padStart(3, "0")} · {g.category}</span>
                  <span style={{ color: "#00FFB4" }}>● ready</span>
                </div>
                <h3 style={{
                  fontFamily: "'Inter', sans-serif", fontWeight: 600,
                  fontSize: i === 0 ? 28 : 20, margin: "10px 0 8px",
                  color: "#fff", letterSpacing: "-0.02em", lineHeight: 1.1,
                }}>{g.name}</h3>
                <p style={{ fontSize: 13, color: "#a3a3b0", margin: 0, lineHeight: 1.5 }}>{g.desc}</p>
                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginTop: 18, paddingTop: 16, borderTop: "1px solid rgba(255,255,255,0.06)" }}>
                  <span style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 16, color: "#fff", fontWeight: 500 }}>{brlM(g.price)}</span>
                  <button style={btnPillM}>reservar →</button>
                </div>
              </article>
            );
          })}
        </div>
      </section>
    </main>
  );
}

const btnSolidM = {
  background: "#fff", color: "#0a0a0d", border: "none",
  padding: "16px 22px", fontFamily: "'JetBrains Mono', monospace",
  fontSize: 12, fontWeight: 500, letterSpacing: "0.08em", textTransform: "lowercase",
  cursor: "pointer", borderRadius: 2, display: "inline-flex", alignItems: "center", gap: 12,
};
const btnGhostM = {
  background: "transparent", color: "#fff",
  border: "1px solid rgba(255,255,255,0.18)",
  padding: "16px 22px", fontFamily: "'JetBrains Mono', monospace",
  fontSize: 12, fontWeight: 500, letterSpacing: "0.08em", textTransform: "lowercase",
  cursor: "pointer", borderRadius: 2,
};
const btnPillM = {
  background: "#00FFB4", color: "#0a0a0d", border: "none",
  padding: "8px 16px", fontFamily: "'JetBrains Mono', monospace",
  fontSize: 11, fontWeight: 500, letterSpacing: "0.08em",
  cursor: "pointer", borderRadius: 2,
};

window.MasculineApp = MasculineApp;
window.btnSolidM = btnSolidM;
window.btnGhostM = btnGhostM;
window.btnPillM = btnPillM;
