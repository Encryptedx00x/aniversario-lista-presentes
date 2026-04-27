/* LIGHT V3 — clean, confident, modern editorial (masculine but not glitchy) */

const GIFTS_L = [
  { id: 1,  name: "Sennheiser HD 660S2",     cat: "Áudio",     price: 4290, qty: 1, top: true,  desc: "Fone aberto. Driver de 38mm para escuta longa.", color: "#1B3A8B" },
  { id: 2,  name: "Salomon XT-6",            cat: "Calçados",  price: 1899, qty: 2, top: false, desc: "Trail running. Cidade ou trilha.",                color: "#2A2A2A" },
  { id: 3,  name: "Yamazaki 12y",            cat: "Bar",       price: 2890, qty: 1, top: true,  desc: "Single malt japonês. Para domingos sem pressa.",   color: "#7A4B1F" },
  { id: 4,  name: "Moonswatch Mission Mars", cat: "Relógios",  price: 2300, qty: 0, top: false, desc: "Bioceramic. Edição vermelha.",                     color: "#A03426" },
  { id: 5,  name: "Herman Miller Aeron",     cat: "Trabalho",  price: 8900, qty: 1, top: false, desc: "Tamanho B. Carbono.",                              color: "#3F3F3F" },
  { id: 6,  name: "Ricoh GR III x",          cat: "Foto",      price: 7990, qty: 1, top: false, desc: "APS-C, 26.1mm. Câmera de bolso definitiva.",        color: "#0F0F12" },
];

function App() {
  const [route, setRoute] = React.useState("home");
  const [cat, setCat] = React.useState("Todos");
  const [authOpen, setAuthOpen] = React.useState(false);
  const [authTab, setAuthTab] = React.useState("signup");
  const [pixCopied, setPixCopied] = React.useState(false);
  const [pixAmount, setPixAmount] = React.useState(150);

  const target = React.useMemo(() => new Date(Date.now() + (47 * 86400 + 8 * 3600 + 23 * 60) * 1000), []);
  const [now, setNow] = React.useState(Date.now());
  React.useEffect(() => { const t = setInterval(() => setNow(Date.now()), 1000); return () => clearInterval(t); }, []);
  const diff = Math.max(0, target.getTime() - now);
  const dd = Math.floor(diff / 86400000);
  const hh = Math.floor((diff % 86400000) / 3600000);
  const mm = Math.floor((diff % 3600000) / 60000);
  const ss = Math.floor((diff % 60000) / 1000);

  const cats = ["Todos", "Áudio", "Calçados", "Bar", "Relógios", "Trabalho", "Foto"];
  const list = cat === "Todos" ? GIFTS_L : GIFTS_L.filter((g) => g.cat === cat);

  return (
    <div style={{ background: "#FAFAF7", color: "#0F0F12", minHeight: "100vh", fontFamily: "'Inter', system-ui, sans-serif" }}>
      <Header route={route} setRoute={setRoute} />
      {route === "home" && <Home dd={dd} hh={hh} mm={mm} ss={ss} setRoute={setRoute} setAuthOpen={setAuthOpen} />}
      {route === "gifts" && <Gifts cats={cats} cat={cat} setCat={setCat} list={list} onReserve={() => setAuthOpen(true)} />}
      {route === "pix" && <Pix amount={pixAmount} setAmount={setPixAmount} copied={pixCopied} onCopy={() => { setPixCopied(true); setTimeout(() => setPixCopied(false), 1500); }} />}
      {route === "my" && <My />}
      <Footer />
      {authOpen && <Auth tab={authTab} setTab={setAuthTab} onClose={() => setAuthOpen(false)} />}
      <Anim />
    </div>
  );
}

function Header({ route, setRoute }) {
  const items = [["home", "Início"], ["gifts", "Lista"], ["pix", "Pix"], ["my", "Reservas"]];
  return (
    <header style={{
      position: "sticky", top: 0, zIndex: 50,
      background: "rgba(250,250,247,0.85)", backdropFilter: "blur(16px)",
      borderBottom: "1px solid rgba(15,15,18,0.08)",
      padding: "20px 56px", display: "grid", gridTemplateColumns: "1fr auto 1fr", alignItems: "center",
    }}>
      <div style={{ fontFamily: "'Inter', sans-serif", fontWeight: 600, fontSize: 12, letterSpacing: "0.18em", textTransform: "uppercase", color: "#6b6b73" }}>
        São Paulo · 12.06.2026
      </div>
      <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
        <span style={{ width: 28, height: 28, background: "#0F0F12", color: "#FAFAF7", display: "inline-flex", alignItems: "center", justifyContent: "center", fontFamily: "'Inter', sans-serif", fontWeight: 700, fontSize: 14 }}>R</span>
        <span style={{ fontFamily: "'Inter', sans-serif", fontWeight: 600, fontSize: 16, letterSpacing: "-0.01em" }}>Rafael, 30.</span>
      </div>
      <nav style={{ display: "flex", gap: 4, justifyContent: "flex-end" }}>
        {items.map(([k, l]) => (
          <button key={k} onClick={() => setRoute(k)} style={{
            padding: "10px 16px", fontSize: 14, fontWeight: 500,
            background: "transparent", color: route === k ? "#0F0F12" : "#6b6b73",
            border: "none", cursor: "pointer", position: "relative",
            fontFamily: "inherit",
          }}>
            {l}
            {route === k && <span style={{ position: "absolute", left: 16, right: 16, bottom: 4, height: 2, background: "#0F0F12" }}></span>}
          </button>
        ))}
      </nav>
    </header>
  );
}

/* ===== HOME ===== */
function Home({ dd, hh, mm, ss, setRoute, setAuthOpen }) {
  return (
    <main>
      {/* HERO — magazine cover */}
      <section style={{ padding: "80px 56px 56px", display: "grid", gridTemplateColumns: "1.3fr 1fr", gap: 40, alignItems: "stretch" }}>
        <div style={{ display: "flex", flexDirection: "column", justifyContent: "space-between" }}>
          <div>
            <div style={{ display: "flex", gap: 10, alignItems: "center", marginBottom: 28 }}>
              <span style={{ width: 8, height: 8, background: "#1B3A8B", borderRadius: 999 }}></span>
              <span style={{ fontFamily: "'Inter', sans-serif", fontWeight: 600, fontSize: 12, letterSpacing: "0.18em", textTransform: "uppercase", color: "#6b6b73" }}>
                Edição N° 30 · Junho de 2026
              </span>
            </div>
            <h1 style={{
              fontFamily: "'Inter', sans-serif", fontWeight: 700,
              fontSize: 144, letterSpacing: "-0.06em", lineHeight: 0.88,
              margin: 0, color: "#0F0F12",
            }}>
              Trinta<br/>anos de<br/>boas<br/>escolhas.
            </h1>
            <p style={{ fontSize: 18, color: "#43434b", marginTop: 32, maxWidth: 480, lineHeight: 1.55 }}>
              Uma curadoria pequena de oito coisas que tenho considerado faz tempo. Reserve uma, ou contribua via Pix — sem pressão, sem firula.
            </p>
            <div style={{ display: "flex", gap: 12, marginTop: 36 }}>
              <button onClick={() => setRoute("gifts")} style={btn.solid}>Ver a lista <span style={{ marginLeft: 10 }}>→</span></button>
              <button onClick={() => setRoute("pix")} style={btn.ghost}>Contribuir via Pix</button>
            </div>
          </div>

          <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", marginTop: 56, paddingTop: 24, borderTop: "1px solid rgba(15,15,18,0.1)" }}>
            {[["08", "presentes"], ["02", "reservados"], ["47%", "financiado"], ["07", "dias de hold"]].map(([v, l]) => (
              <div key={l}>
                <div style={{ fontFamily: "'Inter', sans-serif", fontWeight: 700, fontSize: 32, letterSpacing: "-0.03em" }}>{v}</div>
                <div style={{ fontSize: 12, color: "#6b6b73", marginTop: 4 }}>{l}</div>
              </div>
            ))}
          </div>
        </div>

        {/* Cover image — single bold gradient block */}
        <div style={{
          position: "relative", borderRadius: 4, overflow: "hidden",
          background: "linear-gradient(160deg, #1B3A8B 0%, #0F0F12 100%)",
          minHeight: 640,
        }}>
          <div style={{
            position: "absolute", inset: 0,
            backgroundImage: "radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px)",
            backgroundSize: "4px 4px", mixBlendMode: "overlay",
          }}></div>
          <div style={{ position: "absolute", left: 24, top: 24, color: "rgba(255,255,255,0.7)", fontSize: 11, letterSpacing: "0.18em", fontWeight: 600, textTransform: "uppercase" }}>
            Contagem regressiva
          </div>
          <div style={{ position: "absolute", right: 24, top: 24, color: "rgba(255,255,255,0.7)", fontSize: 11, letterSpacing: "0.18em", fontWeight: 600, textTransform: "uppercase" }}>
            12 · 06 · 26
          </div>

          <div style={{ position: "absolute", left: 32, bottom: 40, color: "#fff" }}>
            <div style={{
              fontFamily: "'Inter', sans-serif", fontWeight: 700,
              fontSize: 240, letterSpacing: "-0.07em", lineHeight: 0.85,
            }}>{String(dd).padStart(2, "0")}</div>
            <div style={{ fontSize: 14, color: "rgba(255,255,255,0.7)", marginTop: 4 }}>dias até a data.</div>
          </div>

          <div style={{ position: "absolute", right: 32, bottom: 40, color: "#fff", textAlign: "right" }}>
            <div style={{ display: "flex", gap: 18 }}>
              {[["h", hh], ["m", mm], ["s", ss]].map(([l, v]) => (
                <div key={l}>
                  <div style={{ fontFamily: "'Inter', sans-serif", fontWeight: 600, fontSize: 28, letterSpacing: "-0.02em" }}>{String(v).padStart(2, "0")}</div>
                  <div style={{ fontSize: 11, color: "rgba(255,255,255,0.6)", marginTop: 2, letterSpacing: "0.16em", textTransform: "uppercase" }}>{l}</div>
                </div>
              ))}
            </div>
          </div>

          {/* Subtle pulsing ring */}
          <div style={{
            position: "absolute", right: -120, top: -120, width: 360, height: 360, borderRadius: 999,
            border: "1px solid rgba(255,255,255,0.18)",
            animation: "ring 6s ease-in-out infinite",
          }}></div>
        </div>
      </section>

      {/* FEATURED */}
      <section style={{ padding: "40px 56px 80px" }}>
        <div style={{ display: "flex", alignItems: "flex-end", justifyContent: "space-between", marginBottom: 32 }}>
          <div>
            <div style={{ fontSize: 12, fontWeight: 600, letterSpacing: "0.18em", color: "#6b6b73", textTransform: "uppercase", marginBottom: 12 }}>Em destaque</div>
            <h2 style={{ fontFamily: "'Inter', sans-serif", fontWeight: 700, fontSize: 56, letterSpacing: "-0.04em", margin: 0 }}>
              O que eu quero mesmo.
            </h2>
          </div>
          <button onClick={() => setRoute("gifts")} style={{
            background: "transparent", border: "none", color: "#0F0F12",
            fontWeight: 600, fontSize: 14, cursor: "pointer", borderBottom: "1px solid #0F0F12", paddingBottom: 4,
          }}>Ver todos os 8 →</button>
        </div>

        <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 20 }}>
          {GIFTS_L.slice(0, 3).map((g) => <Card key={g.id} g={g} big />)}
        </div>
      </section>
    </main>
  );
}

/* ===== CARD ===== */
function Card({ g, big = false, onReserve = () => {} }) {
  const [hover, setHover] = React.useState(false);
  const isOut = g.qty === 0;
  return (
    <article
      onMouseEnter={() => setHover(true)} onMouseLeave={() => setHover(false)}
      style={{
        background: "#fff", border: "1px solid rgba(15,15,18,0.08)", borderRadius: 4,
        overflow: "hidden", transition: "all 320ms cubic-bezier(0.2,0.8,0.2,1)",
        transform: hover ? "translateY(-4px)" : "none",
        boxShadow: hover ? "0 24px 48px -16px rgba(15,15,18,0.18)" : "0 1px 0 rgba(15,15,18,0.03)",
        opacity: isOut ? 0.6 : 1, position: "relative",
      }}>
      <div style={{
        position: "relative", aspectRatio: big ? "4/5" : "5/6",
        background: g.color, overflow: "hidden",
      }}>
        <div style={{
          position: "absolute", inset: 0,
          backgroundImage: "radial-gradient(rgba(255,255,255,0.05) 1px, transparent 1px)",
          backgroundSize: "5px 5px", opacity: 0.5,
        }}></div>
        <div style={{
          position: "absolute", inset: 0, display: "flex", alignItems: "center", justifyContent: "center",
          fontFamily: "'Inter', sans-serif", fontWeight: 700, color: "rgba(255,255,255,0.92)",
          fontSize: big ? 200 : 140, letterSpacing: "-0.06em",
          textShadow: "0 8px 32px rgba(0,0,0,0.25)",
          transform: hover ? "scale(1.04)" : "scale(1)", transition: "transform 600ms cubic-bezier(0.2,0.8,0.2,1)",
        }}>{g.name[0]}</div>
        {g.top && !isOut && (
          <div style={{ position: "absolute", top: 14, right: 14, background: "#fff", color: "#0F0F12", padding: "4px 10px", fontSize: 11, fontWeight: 600, letterSpacing: "0.12em", textTransform: "uppercase" }}>★ Top</div>
        )}
        {isOut && (
          <div style={{ position: "absolute", top: 14, right: 14, background: "rgba(15,15,18,0.85)", color: "#fff", padding: "4px 10px", fontSize: 11, fontWeight: 600, letterSpacing: "0.12em", textTransform: "uppercase" }}>Reservado</div>
        )}
      </div>
      <div style={{ padding: 20 }}>
        <div style={{ display: "flex", justifyContent: "space-between", marginBottom: 8, fontSize: 11, color: "#6b6b73", fontWeight: 600, letterSpacing: "0.14em", textTransform: "uppercase" }}>
          <span>N° {String(g.id).padStart(2, "0")} · {g.cat}</span>
          <span style={{ color: isOut ? "#a03426" : "#1B3A8B" }}>{isOut ? "esgotado" : `${g.qty} disp.`}</span>
        </div>
        <h3 style={{ fontFamily: "'Inter', sans-serif", fontWeight: 600, fontSize: big ? 22 : 18, margin: "0 0 8px", letterSpacing: "-0.02em", lineHeight: 1.15 }}>{g.name}</h3>
        <p style={{ fontSize: 14, color: "#43434b", margin: 0, lineHeight: 1.5 }}>{g.desc}</p>
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginTop: 18, paddingTop: 16, borderTop: "1px solid rgba(15,15,18,0.08)" }}>
          <span style={{ fontWeight: 600, fontSize: 18 }}>R$ {g.price.toLocaleString("pt-BR")}</span>
          {!isOut && (
            <button onClick={onReserve} style={{
              background: "#0F0F12", color: "#fff", border: "none",
              padding: "10px 18px", borderRadius: 999, fontSize: 13, fontWeight: 600,
              cursor: "pointer", fontFamily: "inherit",
            }}>Reservar →</button>
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
        <div style={{ fontSize: 12, fontWeight: 600, letterSpacing: "0.18em", color: "#6b6b73", textTransform: "uppercase", marginBottom: 14 }}>Lista completa · 8 itens</div>
        <h1 style={{ fontFamily: "'Inter', sans-serif", fontWeight: 700, fontSize: 96, letterSpacing: "-0.05em", lineHeight: 0.92, margin: 0 }}>
          Tudo o que tem<br/>na lista.
        </h1>
      </div>

      <div style={{ display: "flex", flexWrap: "wrap", gap: 6, padding: "16px 0", borderTop: "1px solid rgba(15,15,18,0.1)", borderBottom: "1px solid rgba(15,15,18,0.1)", marginBottom: 32 }}>
        {cats.map((c) => (
          <button key={c} onClick={() => setCat(c)} style={{
            padding: "8px 14px", fontSize: 13, fontWeight: 500,
            background: cat === c ? "#0F0F12" : "transparent",
            color: cat === c ? "#FAFAF7" : "#43434b",
            border: "1px solid " + (cat === c ? "#0F0F12" : "rgba(15,15,18,0.12)"),
            borderRadius: 999, cursor: "pointer", fontFamily: "inherit", transition: "all 200ms ease",
          }}>{c}</button>
        ))}
        <span style={{ marginLeft: "auto", alignSelf: "center", fontSize: 12, color: "#6b6b73" }}>{list.length} resultados</span>
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 20 }}>
        {list.map((g) => <Card key={g.id} g={g} onReserve={onReserve} />)}
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
        <div style={{ fontSize: 12, fontWeight: 600, letterSpacing: "0.18em", color: "#6b6b73", textTransform: "uppercase", marginBottom: 14 }}>Contribuir</div>
        <h1 style={{ fontFamily: "'Inter', sans-serif", fontWeight: 700, fontSize: 96, letterSpacing: "-0.05em", lineHeight: 0.92, margin: 0 }}>
          Sem cadastro,<br/>sem pressa.
        </h1>
        <p style={{ fontSize: 16, color: "#43434b", marginTop: 18, maxWidth: 560 }}>
          Se você prefere mandar um valor — qualquer valor — eu junto com outras contribuições e cuido dos itens da lista.
        </p>
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 20 }}>
        <div style={{ background: "#fff", border: "1px solid rgba(15,15,18,0.08)", padding: 32, borderRadius: 4 }}>
          <div style={{ fontSize: 12, fontWeight: 600, letterSpacing: "0.18em", color: "#6b6b73", textTransform: "uppercase", marginBottom: 24 }}>Dados Pix</div>
          <div style={{ display: "grid", gridTemplateColumns: "1fr 200px", gap: 24 }}>
            <div>
              {[["Recebedor", "Rafael S. Andrade"], ["Chave (e-mail)", "rafael.aniv@email.com"], ["Banco", "260 — Nu Pagamentos"], ["Cidade", "São Paulo — SP"]].map(([k, v]) => (
                <div key={k} style={{ marginBottom: 18 }}>
                  <div style={{ fontSize: 11, fontWeight: 600, letterSpacing: "0.16em", color: "#6b6b73", textTransform: "uppercase", marginBottom: 4 }}>{k}</div>
                  <div style={{ fontSize: 15, color: "#0F0F12", fontWeight: 500 }}>{v}</div>
                </div>
              ))}
            </div>
            <div style={{ background: "#fff", border: "1px solid rgba(15,15,18,0.08)", padding: 8, aspectRatio: "1", display: "grid", gridTemplateColumns: "repeat(14, 1fr)", gridTemplateRows: "repeat(14, 1fr)", gap: 1 }}>
              {Array.from({ length: 196 }).map((_, i) => {
                const on = (i * 37 + (i % 11) * 7) % 5 < 2;
                const r = i % 14, c = Math.floor(i / 14);
                const corner = (r < 3 && c < 3) || (r >= 11 && c < 3) || (r < 3 && c >= 11);
                return <div key={i} style={{ background: corner || on ? "#0F0F12" : "transparent" }}></div>;
              })}
            </div>
          </div>
          <div style={{ marginTop: 24, paddingTop: 20, borderTop: "1px solid rgba(15,15,18,0.08)" }}>
            <div style={{ fontSize: 11, fontWeight: 600, letterSpacing: "0.16em", color: "#6b6b73", textTransform: "uppercase", marginBottom: 10 }}>Pix copia e cola</div>
            <div style={{
              fontFamily: "'JetBrains Mono', monospace", fontSize: 12, lineHeight: 1.5,
              background: "#FAFAF7", border: "1px solid rgba(15,15,18,0.08)",
              padding: 14, color: "#0F0F12", wordBreak: "break-all", borderRadius: 4,
            }}>00020126360014BR.GOV.BCB.PIX0114rafael.aniv@email5204000053039865406150.005802BR5919RAFAEL S ANDRADE6009SAO PAULO62070503***6304B7C9</div>
            <button onClick={onCopy} style={{
              marginTop: 14, width: "100%",
              background: copied ? "#1B3A8B" : "#0F0F12", color: "#fff",
              border: "none", padding: "14px", fontSize: 14, fontWeight: 600,
              cursor: "pointer", borderRadius: 4, fontFamily: "inherit",
              transition: "background 200ms ease",
            }}>{copied ? "✓ Copiado" : "Copiar código Pix"}</button>
          </div>
        </div>

        <div style={{ background: "#fff", border: "1px solid rgba(15,15,18,0.08)", padding: 32, borderRadius: 4 }}>
          <div style={{ display: "inline-flex", alignItems: "center", gap: 8, padding: "6px 12px", background: "#EEF1F9", borderRadius: 999, marginBottom: 22 }}>
            <span style={{ width: 6, height: 6, background: "#1B3A8B", borderRadius: 999 }}></span>
            <span style={{ fontSize: 12, color: "#1B3A8B", fontWeight: 600 }}>Contribuindo para: HD 660S2</span>
          </div>

          <div style={{ fontSize: 12, fontWeight: 600, letterSpacing: "0.16em", color: "#6b6b73", textTransform: "uppercase", marginBottom: 12 }}>Valor</div>
          <div style={{ display: "flex", alignItems: "baseline", gap: 12, paddingBottom: 16, borderBottom: "1px solid rgba(15,15,18,0.1)" }}>
            <span style={{ fontFamily: "'Inter', sans-serif", fontWeight: 500, fontSize: 28, color: "#6b6b73" }}>R$</span>
            <input type="number" value={amount} onChange={(e) => setAmount(Number(e.target.value) || 0)} style={{
              flex: 1, background: "transparent", border: "none", outline: "none",
              fontFamily: "'Inter', sans-serif", fontWeight: 700, fontSize: 88,
              letterSpacing: "-0.04em", color: "#0F0F12", padding: 0, lineHeight: 1,
            }} />
          </div>
          <div style={{ display: "flex", flexWrap: "wrap", gap: 8, marginTop: 18 }}>
            {presets.map((v) => (
              <button key={v} onClick={() => setAmount(v)} style={{
                padding: "8px 16px", fontSize: 13, fontWeight: 500,
                background: amount === v ? "#0F0F12" : "transparent",
                color: amount === v ? "#fff" : "#43434b",
                border: "1px solid " + (amount === v ? "#0F0F12" : "rgba(15,15,18,0.12)"),
                borderRadius: 999, cursor: "pointer", fontFamily: "inherit",
              }}>R$ {v}</button>
            ))}
          </div>

          <div style={{ marginTop: 28, display: "grid", gap: 18 }}>
            <Field label="Seu nome" placeholder="Como você assina" />
            <Field label="E-mail" placeholder="voce@exemplo.com" />
            <Field label="Mensagem (opcional)" placeholder="Parabéns, mano…" textarea />
          </div>

          <button style={{
            marginTop: 28, width: "100%", background: "#0F0F12", color: "#fff",
            border: "none", padding: "16px", fontSize: 15, fontWeight: 600,
            cursor: "pointer", borderRadius: 4, fontFamily: "inherit",
          }}>Confirmar contribuição →</button>
        </div>
      </div>
    </main>
  );
}

function Field({ label, placeholder, textarea = false }) {
  const Cmp = textarea ? "textarea" : "input";
  return (
    <div>
      <label style={{ fontSize: 11, fontWeight: 600, letterSpacing: "0.16em", color: "#6b6b73", textTransform: "uppercase" }}>{label}</label>
      <Cmp rows={textarea ? 2 : undefined} placeholder={placeholder} style={{
        width: "100%", marginTop: 6, padding: "10px 0",
        background: "transparent", border: "none", borderBottom: "1px solid rgba(15,15,18,0.12)",
        fontFamily: "inherit", fontSize: 15, color: "#0F0F12", outline: "none", resize: "none",
      }} />
    </div>
  );
}

/* ===== MY ===== */
function My() {
  const data = [
    { ...GIFTS_L[0], status: "reserved", date: "12/04/2026 · 19:42" },
    { ...GIFTS_L[5], status: "confirmed", date: "08/04/2026 · 11:10" },
  ];
  return (
    <main style={{ padding: 56 }}>
      <div style={{ marginBottom: 40 }}>
        <div style={{ fontSize: 12, fontWeight: 600, letterSpacing: "0.18em", color: "#6b6b73", textTransform: "uppercase", marginBottom: 14 }}>Suas reservas</div>
        <h1 style={{ fontFamily: "'Inter', sans-serif", fontWeight: 700, fontSize: 96, letterSpacing: "-0.05em", lineHeight: 0.92, margin: 0 }}>O que você reservou.</h1>
      </div>
      <div style={{ display: "grid", gap: 16 }}>
        {data.map((r) => (
          <article key={r.id} style={{
            background: "#fff", border: "1px solid rgba(15,15,18,0.08)", borderRadius: 4,
            display: "grid", gridTemplateColumns: "200px 1fr auto", overflow: "hidden",
          }}>
            <div style={{ background: r.color, height: 200, position: "relative" }}>
              <div style={{ position: "absolute", inset: 0, display: "flex", alignItems: "center", justifyContent: "center", fontFamily: "'Inter', sans-serif", fontWeight: 700, fontSize: 100, color: "rgba(255,255,255,0.9)", letterSpacing: "-0.06em" }}>{r.name[0]}</div>
            </div>
            <div style={{ padding: 24 }}>
              <div style={{ fontSize: 11, fontWeight: 600, letterSpacing: "0.16em", color: "#6b6b73", textTransform: "uppercase", marginBottom: 8 }}>
                N° {String(r.id).padStart(2, "0")} · {r.cat} · reservado {r.date}
              </div>
              <h3 style={{ fontFamily: "'Inter', sans-serif", fontWeight: 600, fontSize: 24, letterSpacing: "-0.02em", margin: "0 0 6px" }}>{r.name}</h3>
              <p style={{ fontSize: 14, color: "#43434b", margin: 0, maxWidth: 480, lineHeight: 1.5 }}>{r.desc}</p>
              <div style={{ marginTop: 14, display: "inline-flex", alignItems: "center", gap: 8, padding: "4px 10px", background: r.status === "reserved" ? "#EEF1F9" : "#EAF4EE", borderRadius: 999, fontSize: 12, fontWeight: 600, color: r.status === "reserved" ? "#1B3A8B" : "#1d6e3a" }}>
                <span style={{ width: 6, height: 6, background: r.status === "reserved" ? "#1B3A8B" : "#1d6e3a", borderRadius: 999 }}></span>
                {r.status === "reserved" ? "Reservado · expira em 4d 12h" : "Entregue · obrigado"}
              </div>
            </div>
            <div style={{ padding: 24, display: "flex", flexDirection: "column", justifyContent: "center", alignItems: "flex-end", gap: 12 }}>
              <span style={{ fontWeight: 600, fontSize: 22 }}>R$ {r.price.toLocaleString("pt-BR")}</span>
              {r.status === "reserved" ? (
                <div style={{ display: "flex", gap: 8 }}>
                  <button style={btn.ghostSm}>Cancelar</button>
                  <button style={btn.solidSm}>Pagar via Pix →</button>
                </div>
              ) : <span style={{ fontSize: 13, color: "#6b6b73" }}>Arquivado</span>}
            </div>
          </article>
        ))}
      </div>
    </main>
  );
}

/* ===== AUTH ===== */
function Auth({ tab, setTab, onClose }) {
  return (
    <div style={{
      position: "fixed", inset: 0, zIndex: 200,
      background: "rgba(15,15,18,0.36)", backdropFilter: "blur(6px)",
      display: "flex", alignItems: "center", justifyContent: "center",
      animation: "fade 240ms ease",
    }} onClick={onClose}>
      <div onClick={(e) => e.stopPropagation()} style={{
        width: 720, background: "#fff", borderRadius: 4,
        boxShadow: "0 60px 120px -20px rgba(15,15,18,0.36)",
        display: "grid", gridTemplateColumns: "260px 1fr", overflow: "hidden",
        animation: "slideUp 420ms cubic-bezier(0.16,1,0.3,1)",
      }}>
        <div style={{ background: "#1B3A8B", color: "#fff", padding: 28, position: "relative", overflow: "hidden" }}>
          <div style={{ fontSize: 11, fontWeight: 600, letterSpacing: "0.16em", textTransform: "uppercase", color: "rgba(255,255,255,0.7)", marginBottom: 14 }}>Reservando</div>
          <div style={{ fontFamily: "'Inter', sans-serif", fontWeight: 700, fontSize: 30, letterSpacing: "-0.025em", lineHeight: 1.05 }}>Sennheiser<br/>HD 660S2.</div>
          <div style={{ fontSize: 13, color: "rgba(255,255,255,0.7)", marginTop: 12 }}>N° 01 · Áudio · R$ 4.290</div>
          <div style={{ position: "absolute", left: 28, right: 28, bottom: 28, fontSize: 12, color: "rgba(255,255,255,0.7)", lineHeight: 1.5 }}>
            Pra reservar a gente só precisa do seu e-mail. Reserva fica de pé por 7 dias.
          </div>
        </div>
        <div style={{ padding: 32 }}>
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 20 }}>
            <h2 style={{ fontFamily: "'Inter', sans-serif", fontWeight: 700, fontSize: 26, letterSpacing: "-0.02em", margin: 0 }}>
              {tab === "signup" ? "Criar conta" : "Entrar"}
            </h2>
            <button onClick={onClose} style={{ background: "transparent", border: "1px solid rgba(15,15,18,0.12)", color: "#43434b", width: 32, height: 32, cursor: "pointer", fontFamily: "inherit", fontSize: 16, borderRadius: 4 }}>×</button>
          </div>
          <div style={{ display: "inline-flex", padding: 4, background: "#FAFAF7", border: "1px solid rgba(15,15,18,0.08)", borderRadius: 999, marginBottom: 22 }}>
            {[["signup", "Criar"], ["login", "Já tenho"]].map(([k, l]) => (
              <button key={k} onClick={() => setTab(k)} style={{
                padding: "8px 16px", fontSize: 13, fontWeight: 600,
                background: tab === k ? "#0F0F12" : "transparent",
                color: tab === k ? "#fff" : "#43434b",
                border: "none", borderRadius: 999, cursor: "pointer", fontFamily: "inherit",
                transition: "all 200ms ease",
              }}>{l}</button>
            ))}
          </div>
          <div style={{ display: "grid", gap: 16 }}>
            {tab === "signup" && <Field label="Nome" placeholder="Como você se chama" />}
            <Field label="E-mail" placeholder="voce@exemplo.com" />
            <Field label="Senha" placeholder="·······" />
            <button style={{
              marginTop: 8, background: "#0F0F12", color: "#fff",
              border: "none", padding: "14px", fontSize: 14, fontWeight: 600,
              cursor: "pointer", borderRadius: 4, fontFamily: "inherit",
            }}>{tab === "signup" ? "Criar conta e reservar →" : "Entrar e reservar →"}</button>
          </div>
        </div>
      </div>
    </div>
  );
}

/* ===== FOOTER ===== */
function Footer() {
  return (
    <footer style={{ borderTop: "1px solid rgba(15,15,18,0.08)", padding: "40px 56px 56px", background: "#fff" }}>
      <div style={{ display: "grid", gridTemplateColumns: "2fr 1fr 1fr 1fr", gap: 32 }}>
        <div>
          <div style={{ fontFamily: "'Inter', sans-serif", fontWeight: 700, fontSize: 22, letterSpacing: "-0.02em", marginBottom: 8 }}>Rafael, 30.</div>
          <div style={{ fontSize: 13, color: "#6b6b73", maxWidth: 360, lineHeight: 1.5 }}>Obrigado por chegar até aqui. Qualquer dúvida, manda mensagem direto.</div>
        </div>
        {[
          ["Mapa", ["Início", "Lista", "Pix", "Reservas"]],
          ["Contato", ["WhatsApp", "Instagram"]],
          ["Sobre", ["Como funciona", "Política"]],
        ].map(([t, items]) => (
          <div key={t}>
            <div style={{ fontSize: 11, fontWeight: 600, letterSpacing: "0.16em", color: "#6b6b73", textTransform: "uppercase", marginBottom: 12 }}>{t}</div>
            {items.map((i) => <div key={i} style={{ fontSize: 14, color: "#0F0F12", marginBottom: 6 }}>{i}</div>)}
          </div>
        ))}
      </div>
    </footer>
  );
}

const btn = {
  solid:    { background: "#0F0F12", color: "#FAFAF7", border: "none", padding: "16px 26px", fontSize: 14, fontWeight: 600, borderRadius: 999, cursor: "pointer", fontFamily: "inherit", display: "inline-flex", alignItems: "center" },
  ghost:    { background: "transparent", color: "#0F0F12", border: "1px solid rgba(15,15,18,0.18)", padding: "16px 26px", fontSize: 14, fontWeight: 600, borderRadius: 999, cursor: "pointer", fontFamily: "inherit" },
  solidSm:  { background: "#0F0F12", color: "#fff", border: "none", padding: "8px 16px", fontSize: 13, fontWeight: 600, borderRadius: 999, cursor: "pointer", fontFamily: "inherit" },
  ghostSm:  { background: "transparent", color: "#43434b", border: "1px solid rgba(15,15,18,0.12)", padding: "8px 14px", fontSize: 13, fontWeight: 500, borderRadius: 999, cursor: "pointer", fontFamily: "inherit" },
};

function Anim() {
  return <style>{`
    @keyframes ring { 0%, 100% { transform: scale(1); opacity: 0.6; } 50% { transform: scale(1.08); opacity: 1; } }
    @keyframes fade { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: 0.001ms !important; transition-duration: 0.001ms !important; } }
    input::placeholder, textarea::placeholder { color: #9a9aa3; }
    input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }
    ::selection { background: #1B3A8B; color: #fff; }
  `}</style>;
}

window.App = App;
