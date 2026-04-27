/* MASCULINE — Gifts list, Pix, My Gifts, Auth Modal, Animations */

function GiftsM({ cats, filter, setFilter, gifts, hoverId, setHoverId, onReserve }) {
  return (
    <main style={{ padding: "56px" }}>
      <div style={{ display: "grid", gridTemplateColumns: "1.6fr 1fr", gap: 40, alignItems: "end", marginBottom: 40 }}>
        <div>
          <MTag style={{ marginBottom: 18 }}>// 01_gift_index</MTag>
          <h1 style={{
            fontFamily: "'Inter', sans-serif", fontWeight: 700,
            fontSize: 112, letterSpacing: "-0.06em", lineHeight: 0.9,
            margin: 0, color: "#fff",
          }}>
            o catálogo<span style={{ color: "#00FFB4" }}>.</span>
          </h1>
        </div>
        <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 13, color: "#a3a3b0", lineHeight: 1.6, paddingBottom: 8 }}>
          {">"} 8 itens, 3 reservados, 1 esgotado.<br/>
          {">"} reserva expira em 7 dias.<br/>
          {">"} qualquer dúvida, dm.
        </div>
      </div>

      {/* Filter row */}
      <div style={{
        display: "flex", flexWrap: "wrap", gap: 6, padding: "16px 0",
        borderTop: "1px solid rgba(255,255,255,0.08)",
        borderBottom: "1px solid rgba(255,255,255,0.08)", marginBottom: 32,
      }}>
        {cats.map((c) => (
          <button key={c} onClick={() => setFilter(c)} style={{
            padding: "8px 14px", fontFamily: "'JetBrains Mono', monospace",
            fontSize: 11, letterSpacing: "0.14em",
            background: filter === c ? "#00FFB4" : "transparent",
            color: filter === c ? "#0a0a0d" : "#a3a3b0",
            border: "1px solid " + (filter === c ? "#00FFB4" : "rgba(255,255,255,0.12)"),
            borderRadius: 2, cursor: "pointer", transition: "all 200ms ease",
          }}>{c}</button>
        ))}
        <span style={{ marginLeft: "auto", fontFamily: "'JetBrains Mono', monospace", fontSize: 11, color: "#7a7a8a", alignSelf: "center" }}>
          [{String(gifts.length).padStart(2, "0")}/08] visible
        </span>
      </div>

      {/* asymmetric grid */}
      <div style={{ display: "grid", gridTemplateColumns: "repeat(12, 1fr)", gap: 16 }}>
        {gifts.map((g, i) => {
          const span = [4, 4, 4, 6, 3, 3, 6, 6][i % 8];
          const hue = [192, 158, 280, 0, 192, 158, 280, 192][i % 8];
          const isOut = g.qty === 0;
          const hot = hoverId === g.id;
          return (
            <article
              key={g.id}
              onMouseEnter={() => setHoverId(g.id)}
              onMouseLeave={() => setHoverId(null)}
              style={{
                gridColumn: `span ${span}`,
                background: "#0f0f14",
                border: "1px solid " + (hot ? "rgba(0,255,180,0.6)" : "rgba(255,255,255,0.08)"),
                padding: 16, position: "relative", cursor: "pointer",
                transform: hot ? "translateY(-4px)" : "translateY(0)",
                boxShadow: hot ? "0 24px 60px -16px rgba(0,255,180,0.25)" : "none",
                transition: "all 280ms cubic-bezier(0.2, 0.8, 0.2, 1)",
                opacity: isOut ? 0.5 : 1,
              }}
            >
              <GiftCubeM label={g.name[0]} hue={hue} animate={hot || i < 3} />
              {g.featured && !isOut && (
                <div style={{
                  position: "absolute", top: 8, right: 8, zIndex: 3,
                  fontFamily: "'JetBrains Mono', monospace", fontSize: 9,
                  letterSpacing: "0.18em", padding: "4px 8px",
                  background: "#00FFB4", color: "#0a0a0d", borderRadius: 2,
                }}>★ TOP</div>
              )}
              {isOut && (
                <div style={{
                  position: "absolute", top: 8, right: 8, zIndex: 3,
                  fontFamily: "'JetBrains Mono', monospace", fontSize: 9,
                  letterSpacing: "0.18em", padding: "4px 8px",
                  background: "rgba(255,80,80,0.15)", color: "#ff5050",
                  border: "1px solid #ff5050", borderRadius: 2,
                }}>SOLD OUT</div>
              )}

              <div style={{ display: "flex", justifyContent: "space-between", marginTop: 16, fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.16em", color: "#7a7a8a" }}>
                <span>ID-{String(g.id).padStart(3, "0")} · {g.category}</span>
                <span style={{ color: isOut ? "#ff5050" : "#00FFB4" }}>{isOut ? "● none" : `● ${g.qty}`}</span>
              </div>
              <h3 style={{
                fontFamily: "'Inter', sans-serif", fontWeight: 600,
                fontSize: span >= 6 ? 28 : 18,
                margin: "10px 0 6px", color: "#fff", letterSpacing: "-0.02em", lineHeight: 1.15,
              }}>{g.name}</h3>
              <p style={{ fontSize: 13, color: "#a3a3b0", margin: 0, lineHeight: 1.5,
                display: "-webkit-box", WebkitLineClamp: 2, WebkitBoxOrient: "vertical", overflow: "hidden" }}>{g.desc}</p>

              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginTop: 16, paddingTop: 14, borderTop: "1px solid rgba(255,255,255,0.06)" }}>
                <span style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 15, color: "#fff", fontWeight: 500 }}>{brlM(g.price)}</span>
                {!isOut && (
                  <button onClick={onReserve} style={btnPillM}>reservar →</button>
                )}
              </div>
            </article>
          );
        })}
      </div>
    </main>
  );
}

/* ======= PIX ======= */
function PixM({ amount, setAmount, copied, onCopy }) {
  const presets = [50, 100, 150, 250, 500, 1000];
  return (
    <main style={{ padding: 56 }}>
      <div style={{ marginBottom: 40 }}>
        <MTag style={{ marginBottom: 18 }}>// 02_funding_protocol</MTag>
        <h1 style={{
          fontFamily: "'Inter', sans-serif", fontWeight: 700,
          fontSize: 112, letterSpacing: "-0.06em", lineHeight: 0.9, margin: 0, color: "#fff",
        }}>
          contribuir<span style={{ color: "#00C8FF" }}>.</span>
        </h1>
        <p style={{ fontSize: 15, color: "#a3a3b0", marginTop: 18, maxWidth: 560, fontFamily: "'JetBrains Mono', monospace", lineHeight: 1.6 }}>
          {">"} sem login, sem cadastro. transferência direta.
        </p>
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 16 }}>
        {/* LEFT — Pix data */}
        <div style={{ background: "#0f0f14", border: "1px solid rgba(255,255,255,0.08)", padding: 32, position: "relative", overflow: "hidden" }}>
          <div style={{ display: "flex", justifyContent: "space-between", marginBottom: 24 }}>
            <MTag kind="cyan">▲ pix · receiver</MTag>
            <span style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.18em", color: "#7a7a8a" }}>SECURED · TLS</span>
          </div>

          <div style={{ display: "grid", gridTemplateColumns: "1fr 200px", gap: 24 }}>
            <div>
              {[
                ["receiver", "Rafael S. Andrade"],
                ["key_email", "rafael.aniv@email.com"],
                ["bank", "260 — Nu Pagamentos"],
                ["city", "São Paulo — SP"],
              ].map(([k, v]) => (
                <div key={k} style={{ marginBottom: 20 }}>
                  <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.18em", color: "#7a7a8a", textTransform: "uppercase", marginBottom: 6 }}>{k}</div>
                  <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 14, color: "#fff", letterSpacing: "0.02em" }}>{v}</div>
                </div>
              ))}
            </div>
            {/* QR */}
            <div style={{ background: "#fff", padding: 12, aspectRatio: "1", display: "grid", gridTemplateColumns: "repeat(14, 1fr)", gridTemplateRows: "repeat(14, 1fr)", gap: 1 }}>
              {Array.from({ length: 196 }).map((_, i) => {
                const on = (i * 37 + (i % 11) * 7) % 5 < 2;
                const r = i % 14, c = Math.floor(i / 14);
                const corner = (r < 3 && c < 3) || (r >= 11 && c < 3) || (r < 3 && c >= 11);
                return <div key={i} style={{ background: corner || on ? "#0a0a0d" : "transparent" }}></div>;
              })}
            </div>
          </div>

          <div style={{ marginTop: 24, paddingTop: 20, borderTop: "1px solid rgba(255,255,255,0.08)" }}>
            <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.18em", color: "#7a7a8a", marginBottom: 10, textTransform: "uppercase" }}>copy_paste_code</div>
            <div style={{
              fontFamily: "'JetBrains Mono', monospace", fontSize: 11, lineHeight: 1.5,
              background: "#000", border: "1px solid rgba(255,255,255,0.06)",
              padding: 14, color: "#00FFB4", wordBreak: "break-all",
            }}>
              00020126360014BR.GOV.BCB.PIX0114rafael.aniv@email5204000053039865406150.005802BR5919RAFAEL S ANDRADE6009SAO PAULO62070503***6304B7C9
            </div>
            <button onClick={onCopy} style={{
              marginTop: 14, width: "100%",
              background: copied ? "#00FFB4" : "transparent",
              color: copied ? "#0a0a0d" : "#fff",
              border: "1px solid " + (copied ? "#00FFB4" : "rgba(255,255,255,0.18)"),
              padding: "14px", fontFamily: "'JetBrains Mono', monospace", fontSize: 12,
              letterSpacing: "0.12em", cursor: "pointer", borderRadius: 2, transition: "all 200ms ease",
            }}>
              {copied ? "✓ copied to clipboard" : "$ copy --pix-code"}
            </button>
          </div>

          <div style={{
            position: "absolute", right: -80, bottom: -80, width: 240, height: 240, borderRadius: 999,
            background: "radial-gradient(circle, rgba(0,200,255,0.4), transparent 60%)",
            filter: "blur(40px)", pointerEvents: "none", animation: "pulseM 4s ease-in-out infinite",
          }}></div>
        </div>

        {/* RIGHT — form */}
        <div style={{ background: "#0f0f14", border: "1px solid rgba(255,255,255,0.08)", padding: 32 }}>
          <MTag kind="accent" style={{ marginBottom: 18 }}>▲ contribuindo para: hd 660s2</MTag>

          <div style={{ marginBottom: 8, fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.18em", color: "#7a7a8a", textTransform: "uppercase" }}>
            transfer_amount
          </div>
          <div style={{ display: "flex", alignItems: "baseline", gap: 12, paddingBottom: 16, borderBottom: "1px solid rgba(255,255,255,0.08)" }}>
            <span style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 24, color: "#7a7a8a" }}>R$</span>
            <input
              type="number" value={amount}
              onChange={(e) => setAmount(Number(e.target.value) || 0)}
              style={{
                flex: 1, background: "transparent", border: "none", outline: "none",
                color: "#00FFB4", fontFamily: "'JetBrains Mono', monospace", fontWeight: 500,
                fontSize: 88, letterSpacing: "-0.04em", padding: 0, lineHeight: 1,
              }}
            />
          </div>

          <div style={{ display: "flex", flexWrap: "wrap", gap: 6, marginTop: 18 }}>
            {presets.map((v) => (
              <button key={v} onClick={() => setAmount(v)} style={{
                padding: "8px 14px", fontFamily: "'JetBrains Mono', monospace",
                fontSize: 11, letterSpacing: "0.12em",
                background: amount === v ? "#fff" : "transparent",
                color: amount === v ? "#0a0a0d" : "#a3a3b0",
                border: "1px solid " + (amount === v ? "#fff" : "rgba(255,255,255,0.12)"),
                borderRadius: 2, cursor: "pointer", transition: "all 200ms ease",
              }}>R$ {v}</button>
            ))}
          </div>

          <div style={{ marginTop: 28, display: "grid", gap: 16 }}>
            {[
              ["sender_name", "João Pedro Lima"],
              ["sender_email", "joao@example.com"],
            ].map(([k, v]) => (
              <div key={k}>
                <label style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.18em", textTransform: "uppercase", color: "#7a7a8a" }}>{k}</label>
                <input defaultValue={v} style={{
                  width: "100%", marginTop: 6, padding: "10px 0",
                  background: "transparent", border: "none", borderBottom: "1px solid rgba(255,255,255,0.12)",
                  fontFamily: "'JetBrains Mono', monospace", fontSize: 14, color: "#fff", outline: "none",
                }} />
              </div>
            ))}
            <div>
              <label style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.18em", textTransform: "uppercase", color: "#7a7a8a" }}>message_optional</label>
              <textarea rows={2} placeholder="// parabens cara, manda bala" style={{
                width: "100%", marginTop: 6, padding: "10px 0",
                background: "transparent", border: "none", borderBottom: "1px solid rgba(255,255,255,0.12)",
                fontFamily: "'JetBrains Mono', monospace", fontSize: 14, color: "#fff", outline: "none", resize: "none",
              }}></textarea>
            </div>
          </div>

          <button style={{
            marginTop: 28, width: "100%", background: "#00FFB4", color: "#0a0a0d",
            border: "none", padding: "18px", fontFamily: "'JetBrains Mono', monospace",
            fontSize: 13, fontWeight: 500, letterSpacing: "0.12em", cursor: "pointer", borderRadius: 2,
          }}>
            $ ./execute_transfer →
          </button>
          <div style={{ marginTop: 12, fontFamily: "'JetBrains Mono', monospace", fontSize: 10, color: "#7a7a8a", textAlign: "center", letterSpacing: "0.08em" }}>
            // confirmação automática quando o pix compensar
          </div>
        </div>
      </div>
    </main>
  );
}

/* ======= MY GIFTS ======= */
function MyM() {
  const data = [
    { ...SAMPLE_GIFTS_M[0], status: "reserved", date: "12.04.2026 19:42" },
    { ...SAMPLE_GIFTS_M[5], status: "confirmed", date: "08.04.2026 11:10" },
  ];
  return (
    <main style={{ padding: 56 }}>
      <MTag style={{ marginBottom: 18 }}>// 03_my_reservations</MTag>
      <h1 style={{
        fontFamily: "'Inter', sans-serif", fontWeight: 700,
        fontSize: 96, letterSpacing: "-0.06em", lineHeight: 0.9, margin: 0, color: "#fff",
      }}>
        meus presentes<span style={{ color: "#00C8FF" }}>.</span>
      </h1>
      <div style={{ display: "grid", gap: 14, marginTop: 40 }}>
        {data.map((r, i) => (
          <article key={r.id} style={{
            background: "#0f0f14", border: "1px solid rgba(255,255,255,0.08)",
            display: "grid", gridTemplateColumns: "200px 1fr auto", gap: 0,
          }}>
            <div style={{ height: 200, borderRight: "1px solid rgba(255,255,255,0.08)" }}>
              <GiftCubeM label={r.name[0]} hue={[192, 158][i]} />
            </div>
            <div style={{ padding: 24 }}>
              <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.18em", color: "#7a7a8a", marginBottom: 10 }}>
                ID-{String(r.id).padStart(3, "0")} · {r.category} · {r.date}
              </div>
              <h3 style={{ fontFamily: "'Inter', sans-serif", fontWeight: 600, fontSize: 26, color: "#fff", margin: "0 0 8px", letterSpacing: "-0.02em" }}>{r.name}</h3>
              <p style={{ fontSize: 13, color: "#a3a3b0", margin: 0, lineHeight: 1.5, maxWidth: 480 }}>{r.desc}</p>
              <div style={{ marginTop: 16, fontFamily: "'JetBrains Mono', monospace", fontSize: 11, letterSpacing: "0.16em" }}>
                {r.status === "reserved" && (
                  <span style={{ color: "#00FFB4" }}>
                    ● STATUS: RESERVED · expires in 4d 12h
                  </span>
                )}
                {r.status === "confirmed" && (
                  <span style={{ color: "#00C8FF" }}>
                    ✓ STATUS: DELIVERED · obrigado, cara
                  </span>
                )}
              </div>
            </div>
            <div style={{ padding: 24, display: "flex", flexDirection: "column", justifyContent: "center", alignItems: "flex-end", gap: 12 }}>
              <span style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 22, color: "#fff", fontWeight: 500 }}>{brlM(r.price)}</span>
              {r.status === "reserved" ? (
                <div style={{ display: "flex", gap: 8 }}>
                  <button style={{ ...btnGhostM, padding: "8px 14px", fontSize: 10 }}>cancel</button>
                  <button style={{ ...btnPillM, padding: "8px 14px" }}>pay via pix →</button>
                </div>
              ) : (
                <span style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 11, color: "#7a7a8a" }}>// archived</span>
              )}
            </div>
          </article>
        ))}
      </div>
    </main>
  );
}

/* ======= AUTH MODAL ======= */
function AuthM({ tab, setTab, onClose }) {
  return (
    <div style={{
      position: "fixed", inset: 0, zIndex: 200,
      background: "rgba(0,0,0,0.72)", backdropFilter: "blur(8px)",
      display: "flex", alignItems: "center", justifyContent: "center",
      animation: "modalFadeM 240ms ease",
    }} onClick={onClose}>
      <div onClick={(e) => e.stopPropagation()} style={{
        width: 720, background: "#0a0a0d",
        border: "1px solid rgba(0,255,180,0.3)",
        boxShadow: "0 40px 100px -20px rgba(0,255,180,0.2)",
        display: "grid", gridTemplateColumns: "260px 1fr",
        animation: "modalSlideM 420ms cubic-bezier(0.16, 1, 0.3, 1)",
      }}>
        {/* side */}
        <div style={{ background: "linear-gradient(180deg, #0f0f14, #07070a)", padding: 28, position: "relative", overflow: "hidden", borderRight: "1px solid rgba(255,255,255,0.06)" }}>
          <MTag kind="accent">▲ session_init</MTag>
          <div style={{
            fontFamily: "'Inter', sans-serif", fontWeight: 700,
            fontSize: 32, color: "#fff", marginTop: 18, lineHeight: 1.05, letterSpacing: "-0.03em",
          }}>sennheiser<br/>hd 660s2.</div>
          <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.18em", color: "#7a7a8a", marginTop: 14 }}>
            ID-001 · AUDIO · R$ 4.290
          </div>
          <div style={{ position: "absolute", left: 0, right: 0, bottom: 28, padding: "0 28px", fontFamily: "'JetBrains Mono', monospace", fontSize: 11, color: "#a3a3b0", lineHeight: 1.5 }}>
            {">"} login pra reservar.<br/>{">"} 7 dias de hold.
          </div>
          <div style={{ position: "absolute", right: -50, bottom: -50, width: 180, height: 180, borderRadius: 999, background: "radial-gradient(circle, rgba(0,255,180,0.6), transparent 60%)", filter: "blur(28px)", pointerEvents: "none", animation: "pulseM 4s ease-in-out infinite" }}></div>
        </div>
        {/* form */}
        <div style={{ padding: 32 }}>
          <div style={{ display: "flex", justifyContent: "space-between", marginBottom: 22 }}>
            <h2 style={{ fontFamily: "'Inter', sans-serif", fontWeight: 700, fontSize: 28, color: "#fff", margin: 0, letterSpacing: "-0.02em" }}>
              {tab === "signup" ? "create_account" : "sign_in"}
            </h2>
            <button onClick={onClose} style={{ background: "transparent", border: "1px solid rgba(255,255,255,0.12)", color: "#a3a3b0", width: 32, height: 32, fontFamily: "'JetBrains Mono', monospace", cursor: "pointer", borderRadius: 2 }}>×</button>
          </div>
          <div style={{ display: "inline-flex", padding: 4, background: "rgba(255,255,255,0.04)", border: "1px solid rgba(255,255,255,0.08)", borderRadius: 2, marginBottom: 22 }}>
            {[["signup", "novo"], ["login", "existente"]].map(([k, l]) => (
              <button key={k} onClick={() => setTab(k)} style={{
                padding: "8px 16px", fontFamily: "'JetBrains Mono', monospace",
                fontSize: 11, letterSpacing: "0.14em", border: "none", borderRadius: 2, cursor: "pointer",
                background: tab === k ? "#00FFB4" : "transparent",
                color: tab === k ? "#0a0a0d" : "#a3a3b0",
                transition: "all 200ms ease",
              }}>{l}</button>
            ))}
          </div>

          <div style={{ display: "grid", gap: 16 }}>
            {tab === "signup" && <FieldM label="name" placeholder="seu nome" />}
            <FieldM label="email" placeholder="voce@example.com" />
            <FieldM label="password" placeholder="·······" type="password" />
            <button style={{
              marginTop: 8, background: "#00FFB4", color: "#0a0a0d",
              border: "none", padding: "14px", fontFamily: "'JetBrains Mono', monospace",
              fontSize: 12, fontWeight: 500, letterSpacing: "0.14em", cursor: "pointer", borderRadius: 2,
            }}>
              $ ./{tab === "signup" ? "register_and_reserve" : "auth_and_reserve"} →
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

function FieldM({ label, placeholder, type = "text" }) {
  return (
    <div>
      <label style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.18em", textTransform: "uppercase", color: "#7a7a8a" }}>{label}</label>
      <input type={type} placeholder={placeholder} style={{
        width: "100%", marginTop: 6, padding: "10px 0",
        background: "transparent", border: "none", borderBottom: "1px solid rgba(255,255,255,0.12)",
        fontFamily: "'JetBrains Mono', monospace", fontSize: 14, color: "#fff", outline: "none",
      }} />
    </div>
  );
}

/* ======= GLOBAL ANIMATIONS ======= */
function Animations() {
  return (
    <style>{`
      @keyframes pulseM {
        0%, 100% { opacity: 0.65; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.06); }
      }
      @keyframes spinM {
        from { transform: translate(-50%, -50%) rotate(0deg); }
        to   { transform: translate(-50%, -50%) rotate(360deg); }
      }
      @keyframes scanM {
        0% { top: -2px; opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { top: 102%; opacity: 0; }
      }
      @keyframes auroraM {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(40px, 30px) scale(1.15); }
      }
      @keyframes tickerM {
        from { transform: translateX(0); }
        to   { transform: translateX(-50%); }
      }
      @keyframes progressFlow {
        0%, 100% { box-shadow: 0 0 16px rgba(0,255,180,0.5); }
        50% { box-shadow: 0 0 28px rgba(0,255,180,0.85); }
      }
      @keyframes modalFadeM {
        from { opacity: 0; }
        to { opacity: 1; }
      }
      @keyframes modalSlideM {
        from { opacity: 0; transform: translateY(24px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
      }
      @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
          animation-duration: 0.001ms !important;
          transition-duration: 0.001ms !important;
        }
      }
      input::placeholder { color: #4a4a55; }
      input::-webkit-outer-spin-button, input::-webkit-inner-spin-button {
        -webkit-appearance: none; margin: 0;
      }
      input[type=number] { -moz-appearance: textfield; }
      ::selection { background: #00FFB4; color: #0a0a0d; }
      body { background: #0a0a0d; }
    `}</style>
  );
}

Object.assign(window, { GiftsM, PixM, MyM, AuthM, Animations });
