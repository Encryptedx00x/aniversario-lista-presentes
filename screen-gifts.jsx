/* GIFT LIST — index page with filters + asymmetric grid */

function GiftListScreen() {
  const [cat, setCat] = React.useState("Todas");
  const [sort, setSort] = React.useState("Disponíveis");
  const [hover, setHover] = React.useState(null);

  const cats = ["Todas", "Cozinha", "Livros", "Música", "Memória", "Casa"];
  const sorts = ["Disponíveis", "Menor preço", "Maior preço", "Destaques"];

  let gifts = [...SAMPLE_GIFTS];
  if (cat !== "Todas") gifts = gifts.filter((g) => g.category === cat);
  if (sort === "Menor preço") gifts.sort((a, b) => a.price - b.price);
  if (sort === "Maior preço") gifts.sort((a, b) => b.price - a.price);
  if (sort === "Destaques") gifts.sort((a, b) => Number(b.featured) - Number(a.featured));
  if (sort === "Disponíveis") gifts.sort((a, b) => b.qty - a.qty);

  return (
    <div style={{
      width: 1280, background: "#F6F2EA", color: "#0E0E12",
      fontFamily: "'Söhne','Inter',sans-serif",
    }}>
      {/* Mini header */}
      <header style={{ padding: "20px 56px", borderBottom: "1px solid #ECE6DA", display: "flex", justifyContent: "space-between", alignItems: "center" }}>
        <div style={{ fontFamily: "'Fraunces', serif", fontStyle: "italic", fontWeight: 300, fontSize: 22, letterSpacing: "-0.02em" }}>
          aniversários — <span style={{ color: "#5E2AC9" }}>Mariana ·30</span>
        </div>
        <nav style={{ display: "flex", gap: 26, fontSize: 14 }}>
          <a href="#" style={{ color: "#3a363f", textDecoration: "none" }}>Início</a>
          <a href="#" style={{ color: "#0E0E12", fontWeight: 600, textDecoration: "none", borderBottom: "1px solid #0E0E12", paddingBottom: 2 }}>Presentes</a>
          <a href="#" style={{ color: "#3a363f", textDecoration: "none" }}>Pix</a>
          <a href="#" style={{ color: "#3a363f", textDecoration: "none" }}>Meus presentes</a>
        </nav>
      </header>

      {/* Hero strip */}
      <section style={{ padding: "56px 56px 32px", display: "grid", gridTemplateColumns: "1.6fr 1fr", gap: 40, alignItems: "end" }}>
        <div>
          <Tag style={{ marginBottom: 18 }}>capítulo 02 · todos os presentes</Tag>
          <h1 style={{
            fontFamily: "'Fraunces', serif", fontWeight: 300,
            fontSize: 96, margin: 0, letterSpacing: "-0.045em", lineHeight: 0.95,
          }}>
            Vinte e quatro <i>coisas</i><br/>que ela quer.
          </h1>
        </div>
        <div style={{ fontSize: 15, color: "#3a363f", lineHeight: 1.55, paddingBottom: 12 }}>
          Cada item já tem o link do produto, mas você não <b>precisa</b> comprar pelo link —
          se quiser, contribua o valor via Pix e eu cuido. Reservas valem por 7 dias.
        </div>
      </section>

      {/* Filter row */}
      <section style={{ padding: "0 56px 24px" }}>
        <div style={{
          display: "grid", gridTemplateColumns: "1fr auto auto auto",
          gap: 12, alignItems: "center", paddingBottom: 18,
          borderBottom: "1px solid #ECE6DA",
        }}>
          <div style={{ position: "relative" }}>
            <input
              placeholder="Buscar por nome ou categoria…"
              style={{
                width: "100%", padding: "14px 16px 14px 40px",
                background: "#fff", border: "1px solid #ECE6DA",
                borderRadius: 4, fontSize: 14, fontFamily: "inherit",
                color: "#0E0E12", outline: "none",
              }}
            />
            <span style={{ position: "absolute", left: 14, top: "50%", transform: "translateY(-50%)", color: "#9a8fb6", fontSize: 16 }}>⌕</span>
          </div>
          <div style={{ display: "flex", gap: 6, padding: 4, background: "#fff", border: "1px solid #ECE6DA", borderRadius: 999 }}>
            {cats.map((c) => (
              <button key={c} onClick={() => setCat(c)} style={{
                padding: "8px 14px", fontSize: 13, fontWeight: 500,
                border: "none", borderRadius: 999, cursor: "pointer",
                background: cat === c ? "#0E0E12" : "transparent",
                color: cat === c ? "#F6F2EA" : "#3a363f",
                transition: "all 200ms ease",
              }}>{c}</button>
            ))}
          </div>
          <select
            value={sort} onChange={(e) => setSort(e.target.value)}
            style={{
              padding: "12px 16px", background: "#fff", border: "1px solid #ECE6DA",
              borderRadius: 4, fontSize: 13, fontFamily: "inherit", color: "#0E0E12", cursor: "pointer",
            }}
          >
            {sorts.map((s) => <option key={s}>{s}</option>)}
          </select>
          <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 11, letterSpacing: "0.14em", color: "#9a8fb6", textTransform: "uppercase" }}>
            {String(gifts.length).padStart(2, "0")} resultados
          </div>
        </div>
      </section>

      {/* Grid — asymmetric: rows of 12 cols, alternating spans */}
      <section style={{ padding: "32px 56px 80px" }}>
        <div style={{ display: "grid", gridTemplateColumns: "repeat(12, 1fr)", gap: 24 }}>
          {gifts.map((g, i) => {
            const isOut = g.qty === 0;
            // alternating layout pattern
            const span = [5, 4, 3, 4, 4, 4][i % 6];
            const offsetY = [0, 60, 24, 0, 40, 12][i % 6];
            return (
              <article
                key={g.id}
                onMouseEnter={() => setHover(g.id)}
                onMouseLeave={() => setHover(null)}
                style={{
                  gridColumn: `span ${span}`,
                  marginTop: offsetY,
                  background: isOut ? "#ECE6DA" : "#fff",
                  border: "1px solid #ECE6DA",
                  borderRadius: 4, padding: 16,
                  position: "relative", cursor: "pointer",
                  transform: hover === g.id ? "translateY(-6px)" : "translateY(0)",
                  boxShadow: hover === g.id ? "0 24px 40px -16px rgba(14,14,18,0.18)" : "none",
                  transition: "transform 320ms cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow 320ms",
                  opacity: isOut ? 0.55 : 1,
                  filter: isOut ? "grayscale(0.4)" : "none",
                }}
              >
                {/* Featured stamp */}
                {g.featured && !isOut && (
                  <div style={{
                    position: "absolute", top: 10, right: 10, zIndex: 2,
                    fontFamily: "'Fraunces', serif", fontStyle: "italic", fontWeight: 300,
                    fontSize: 13, color: "#5E2AC9",
                    background: "#F6F2EA", padding: "4px 10px",
                    border: "1px solid #5E2AC9", borderRadius: 999,
                  }}>★ destaque</div>
                )}
                {isOut && (
                  <div style={{
                    position: "absolute", top: 10, right: 10, zIndex: 2,
                    fontFamily: "'JetBrains Mono', monospace", fontSize: 10,
                    letterSpacing: "0.16em", textTransform: "uppercase",
                    background: "#0E0E12", color: "#F6F2EA",
                    padding: "5px 10px", borderRadius: 2,
                  }}>já reservado</div>
                )}

                <GiftVisual tone={g.tone} label={g.name[0]} ratio={span >= 5 ? "5/6" : "4/5"} />

                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginTop: 16, fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.14em", textTransform: "uppercase", color: "#9a8fb6" }}>
                  <span>N° {String(g.id).padStart(2, "0")} · {g.category}</span>
                  <span>{g.qty} disp.</span>
                </div>
                <h3 style={{
                  fontFamily: "'Fraunces', serif", fontWeight: 400,
                  fontSize: span >= 5 ? 30 : 22,
                  margin: "10px 0 6px", letterSpacing: "-0.02em", lineHeight: 1.05,
                }}>{g.name}</h3>
                <p style={{ fontSize: 13, color: "#3a363f", margin: 0, lineHeight: 1.5,
                  display: "-webkit-box", WebkitLineClamp: 2, WebkitBoxOrient: "vertical", overflow: "hidden" }}>
                  {g.desc}
                </p>

                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginTop: 18, paddingTop: 16, borderTop: "1px solid #ECE6DA" }}>
                  <span style={{ fontFamily: "'Fraunces', serif", fontSize: 20 }}>{brl(g.price)}</span>
                  {isOut ? (
                    <span style={{ fontSize: 12, color: "#7a7480", fontStyle: "italic" }}>obrigada ✓</span>
                  ) : (
                    <div style={{ display: "flex", gap: 8 }}>
                      <button style={{
                        background: "transparent", color: "#3a363f",
                        border: "1px solid rgba(14,14,18,0.16)", borderRadius: 999,
                        padding: "8px 12px", fontSize: 12, fontWeight: 500, cursor: "pointer",
                      }}>Pix</button>
                      <button style={{
                        background: "#0E0E12", color: "#F6F2EA",
                        border: "none", borderRadius: 999,
                        padding: "8px 16px", fontSize: 12, fontWeight: 600, cursor: "pointer",
                      }}>Reservar →</button>
                    </div>
                  )}
                </div>
              </article>
            );
          })}
        </div>
      </section>
    </div>
  );
}

window.GiftListScreen = GiftListScreen;
