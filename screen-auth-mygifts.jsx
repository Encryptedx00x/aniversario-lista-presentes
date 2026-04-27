/* AUTH MODAL + MY GIFTS screens */

function AuthModalScreen() {
  const [tab, setTab] = React.useState("signup");

  return (
    <div style={{
      width: 1280, height: 820, position: "relative",
      background: "#F6F2EA", color: "#0E0E12",
      fontFamily: "'Söhne','Inter',sans-serif", overflow: "hidden",
    }}>
      {/* Page behind, blurred */}
      <div style={{ position: "absolute", inset: 0, filter: "blur(6px) brightness(0.96)", pointerEvents: "none" }}>
        <header style={{ padding: "20px 56px", borderBottom: "1px solid #ECE6DA", display: "flex", justifyContent: "space-between", alignItems: "center" }}>
          <div style={{ fontFamily: "'Fraunces', serif", fontStyle: "italic", fontWeight: 300, fontSize: 22 }}>
            aniversários — <span style={{ color: "#5E2AC9" }}>Mariana ·30</span>
          </div>
          <nav style={{ display: "flex", gap: 26, fontSize: 14, color: "#3a363f" }}>
            {["Início", "Presentes", "Pix", "Meus presentes"].map((l) => <span key={l}>{l}</span>)}
          </nav>
        </header>
        <section style={{ padding: 56 }}>
          <h1 style={{ fontFamily: "'Fraunces', serif", fontWeight: 300, fontSize: 96, letterSpacing: "-0.045em", lineHeight: 0.95, margin: 0 }}>
            Vinte e quatro <i>coisas</i><br/>que ela quer.
          </h1>
        </section>
      </div>

      {/* Backdrop */}
      <div style={{
        position: "absolute", inset: 0,
        background: "rgba(14,14,18,0.45)",
        backdropFilter: "blur(2px)",
      }}></div>

      {/* Modal */}
      <div style={{
        position: "absolute", left: "50%", top: "50%",
        transform: "translate(-50%, -50%)",
        width: 720, background: "#F6F2EA", borderRadius: 4,
        boxShadow: "0 60px 120px -30px rgba(14,14,18,0.5)",
        display: "grid", gridTemplateColumns: "260px 1fr", overflow: "hidden",
      }}>
        {/* Side panel — editorial */}
        <div style={{
          background: "#0E0E12", color: "#F6F2EA", padding: 28,
          display: "flex", flexDirection: "column", justifyContent: "space-between",
          position: "relative", overflow: "hidden",
        }}>
          <div>
            <Tag kind="default" style={{ background: "rgba(255,255,255,0.08)", color: "#c6becf", border: "none" }}>★ você está reservando</Tag>
            <div style={{
              fontFamily: "'Fraunces', serif", fontWeight: 300, fontStyle: "italic",
              fontSize: 36, marginTop: 18, letterSpacing: "-0.03em", lineHeight: 1.05,
            }}>Cafeteira<br/>italiana<br/>Bialetti.</div>
            <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.16em", color: "#9a8fb6", marginTop: 18, textTransform: "uppercase" }}>
              N° 01 · cozinha · R$ 289,90
            </div>
          </div>
          <div style={{ fontSize: 12, color: "#9a8fb6", lineHeight: 1.5, position: "relative", zIndex: 2 }}>
            Pra reservar, a gente só precisa do seu nome — assim eu não compro repetido.
          </div>

          {/* halo */}
          <div style={{
            position: "absolute", right: -60, bottom: -60, width: 200, height: 200,
            background: "radial-gradient(circle, rgba(94,42,201,0.7), transparent 70%)",
            borderRadius: 999, filter: "blur(20px)", pointerEvents: "none",
          }}></div>
        </div>

        {/* Form */}
        <div style={{ padding: 36 }}>
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 28 }}>
            <h2 style={{ fontFamily: "'Fraunces', serif", fontWeight: 300, fontSize: 32, margin: 0, letterSpacing: "-0.02em" }}>
              {tab === "signup" ? "Bem-vindo." : "De volta."}
            </h2>
            <button style={{ background: "transparent", border: "none", fontSize: 18, color: "#7a7480", cursor: "pointer" }}>×</button>
          </div>

          {/* Segmented tabs */}
          <div style={{ display: "inline-flex", padding: 4, background: "#fff", border: "1px solid #ECE6DA", borderRadius: 999, marginBottom: 24 }}>
            {[["signup", "Criar conta"], ["login", "Entrar"]].map(([k, l]) => (
              <button key={k} onClick={() => setTab(k)} style={{
                padding: "8px 18px", fontSize: 13, fontWeight: 600,
                border: "none", borderRadius: 999, cursor: "pointer",
                background: tab === k ? "#0E0E12" : "transparent",
                color: tab === k ? "#F6F2EA" : "#3a363f",
                transition: "all 200ms ease",
              }}>{l}</button>
            ))}
          </div>

          {tab === "signup" ? (
            <div style={{ display: "grid", gap: 18 }}>
              <Field label="Nome" placeholder="Seu nome completo" />
              <Field label="E-mail" placeholder="voce@exemplo.com" />
              <Field label="Telefone" placeholder="(opcional)" />
              <Field label="Senha" placeholder="·······" type="password" />
              <button style={btnPrimary}>Criar conta e reservar →</button>
            </div>
          ) : (
            <div style={{ display: "grid", gap: 18 }}>
              <Field label="E-mail" placeholder="voce@exemplo.com" />
              <Field label="Senha" placeholder="·······" type="password" />
              <button style={btnPrimary}>Entrar e reservar →</button>
            </div>
          )}

          <div style={{ marginTop: 18, fontSize: 12, color: "#7a7480", textAlign: "center" }}>
            ou {tab === "signup" ? <a href="#" onClick={(e) => { e.preventDefault(); setTab("login"); }} style={{ color: "#5E2AC9" }}>já tenho conta</a>
                                  : <a href="#" onClick={(e) => { e.preventDefault(); setTab("signup"); }} style={{ color: "#5E2AC9" }}>criar conta agora</a>}
          </div>
        </div>
      </div>
    </div>
  );
}

const btnPrimary = {
  background: "#0E0E12", color: "#F6F2EA",
  border: "none", borderRadius: 999, padding: "16px",
  fontSize: 14, fontWeight: 600, cursor: "pointer",
  marginTop: 8,
};

function Field({ label, placeholder, type = "text" }) {
  return (
    <div>
      <label style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.16em", textTransform: "uppercase", color: "#9a8fb6" }}>{label}</label>
      <input type={type} placeholder={placeholder} style={{
        width: "100%", marginTop: 6, padding: "10px 0",
        border: "none", borderBottom: "1px solid #ECE6DA",
        background: "transparent", fontSize: 16, fontFamily: "inherit", outline: "none", color: "#0E0E12",
      }} />
    </div>
  );
}

/* MY GIFTS */
function MyGiftsScreen() {
  const reservations = [
    { ...SAMPLE_GIFTS[0], status: "reserved",  date: "12/04/2026 · 19:42" },
    { ...SAMPLE_GIFTS[2], status: "confirmed", date: "08/04/2026 · 11:10" },
  ];
  return (
    <div style={{ width: 1280, background: "#F6F2EA", color: "#0E0E12", fontFamily: "'Söhne','Inter',sans-serif" }}>
      <header style={{ padding: "20px 56px", borderBottom: "1px solid #ECE6DA", display: "flex", justifyContent: "space-between", alignItems: "center" }}>
        <div style={{ fontFamily: "'Fraunces', serif", fontStyle: "italic", fontWeight: 300, fontSize: 22 }}>
          aniversários — <span style={{ color: "#5E2AC9" }}>Mariana ·30</span>
        </div>
        <nav style={{ display: "flex", gap: 26, fontSize: 14 }}>
          {["Início", "Presentes", "Pix", "Meus presentes"].map((l) => (
            <a key={l} href="#" style={{
              color: l === "Meus presentes" ? "#0E0E12" : "#3a363f", textDecoration: "none",
              fontWeight: l === "Meus presentes" ? 600 : 400,
              borderBottom: l === "Meus presentes" ? "1px solid #0E0E12" : "1px solid transparent", paddingBottom: 2,
            }}>{l}</a>
          ))}
        </nav>
      </header>

      <section style={{ padding: "56px 56px 24px" }}>
        <Tag style={{ marginBottom: 16 }}>capítulo 04 · suas reservas</Tag>
        <h1 style={{ fontFamily: "'Fraunces', serif", fontWeight: 300, fontSize: 88, letterSpacing: "-0.045em", lineHeight: 0.95, margin: 0 }}>
          O que <i>você</i> reservou.
        </h1>
        <p style={{ fontSize: 15, color: "#3a363f", marginTop: 18, maxWidth: 520, lineHeight: 1.55 }}>
          Reservas valem por 7 dias. Se mudar de ideia, sem problema — pode cancelar e voltar pra lista.
        </p>
      </section>

      <section style={{ padding: "24px 56px 80px" }}>
        <div style={{ display: "grid", gap: 20 }}>
          {reservations.map((r) => (
            <article key={r.id} style={{
              background: "#fff", border: "1px solid #ECE6DA", borderRadius: 4,
              padding: 0, display: "grid", gridTemplateColumns: "180px 1fr auto",
              gap: 28, alignItems: "stretch", overflow: "hidden",
            }}>
              <div style={{ height: 180 }}>
                <GiftVisual tone={r.tone} label={r.name[0]} ratio="1" style={{ height: "100%", borderRadius: 0 }} />
              </div>
              <div style={{ padding: "24px 0", display: "flex", flexDirection: "column", justifyContent: "center" }}>
                <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.16em", color: "#9a8fb6", textTransform: "uppercase", marginBottom: 8 }}>
                  N° {String(r.id).padStart(2, "0")} · {r.category} · reservado em {r.date}
                </div>
                <h3 style={{ fontFamily: "'Fraunces', serif", fontWeight: 400, fontSize: 28, letterSpacing: "-0.02em", margin: "0 0 8px" }}>{r.name}</h3>
                <p style={{ fontSize: 14, color: "#3a363f", margin: 0, lineHeight: 1.5, maxWidth: 520 }}>{r.desc}</p>
                <div style={{ display: "flex", gap: 12, marginTop: 16 }}>
                  {r.status === "reserved" && (
                    <span style={{
                      display: "inline-flex", alignItems: "center", gap: 8,
                      fontFamily: "'JetBrains Mono', monospace", fontSize: 10,
                      letterSpacing: "0.14em", textTransform: "uppercase",
                      color: "#5E2AC9",
                    }}>
                      <span style={{ width: 6, height: 6, background: "#5E2AC9", borderRadius: 999 }}></span>
                      reservado
                    </span>
                  )}
                  {r.status === "confirmed" && (
                    <span style={{
                      display: "inline-flex", alignItems: "center", gap: 8,
                      fontFamily: "'JetBrains Mono', monospace", fontSize: 10,
                      letterSpacing: "0.14em", textTransform: "uppercase",
                      color: "#0E0E12",
                    }}>
                      <span>✓</span> entregue · obrigada
                    </span>
                  )}
                </div>
              </div>
              <div style={{ padding: 24, display: "flex", flexDirection: "column", justifyContent: "center", alignItems: "flex-end", gap: 10 }}>
                <span style={{ fontFamily: "'Fraunces', serif", fontSize: 26 }}>{brl(r.price)}</span>
                {r.status === "reserved" ? (
                  <div style={{ display: "flex", gap: 8 }}>
                    <button style={{
                      background: "transparent", color: "#3a363f",
                      border: "1px solid rgba(14,14,18,0.16)", borderRadius: 999,
                      padding: "8px 14px", fontSize: 12, fontWeight: 500, cursor: "pointer",
                    }}>Cancelar</button>
                    <button style={{
                      background: "#0E0E12", color: "#F6F2EA",
                      border: "none", borderRadius: 999,
                      padding: "8px 16px", fontSize: 12, fontWeight: 600, cursor: "pointer",
                    }}>Dar via Pix →</button>
                  </div>
                ) : (
                  <span style={{ fontFamily: "'Fraunces', serif", fontStyle: "italic", color: "#7a7480", fontSize: 14 }}>com carinho ✦</span>
                )}
              </div>
            </article>
          ))}
        </div>
      </section>
    </div>
  );
}

window.AuthModalScreen = AuthModalScreen;
window.MyGiftsScreen = MyGiftsScreen;
