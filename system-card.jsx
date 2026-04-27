/* System tokens card + key art */

function SystemCard() {
  const swatches = [
    { name: "Ink", hex: "#0E0E12", note: "fundo escuro / títulos" },
    { name: "Paper", hex: "#F6F2EA", note: "fundo claro / superfícies" },
    { name: "Bone", hex: "#ECE6DA", note: "linhas / divisores" },
    { name: "Halo", hex: "#5E2AC9", note: "acento — uso 5%" },
    { name: "Halo Soft", hex: "#E8DEFE", note: "estados / chips" },
    { name: "Ember", hex: "#D9572A", note: "alerta / esgotado" },
  ];
  const types = [
    { name: "Display Serif", font: "'Fraunces', serif", weight: 300, italic: true,  sample: "celebração", size: 56, tracking: "-0.04em" },
    { name: "Heading Sans",  font: "'Söhne', 'Inter', sans-serif", weight: 700, italic: false, sample: "Lista de presentes", size: 28, tracking: "-0.02em" },
    { name: "Body",          font: "'Söhne', 'Inter', sans-serif", weight: 400, italic: false, sample: "Para começar manhãs com calma e cheiro de café passado.", size: 16, tracking: "0" },
    { name: "Mono Caption",  font: "'JetBrains Mono', monospace",  weight: 500, italic: false, sample: "N° 27 — COZINHA — DISPONÍVEL", size: 11, tracking: "0.16em" },
  ];
  return (
    <div style={{
      background: "#F6F2EA", padding: 56, color: "#0E0E12",
      fontFamily: "'Söhne','Inter',sans-serif",
      display: "grid", gridTemplateColumns: "1fr 1fr", gap: 56,
    }}>
      {/* LEFT — manifesto */}
      <div>
        <Tag kind="default" style={{ marginBottom: 18 }}>Conceito · v1</Tag>
        <h1 style={{
          fontFamily: "'Fraunces', serif", fontWeight: 300, fontStyle: "italic",
          fontSize: 88, lineHeight: 0.95, margin: 0, letterSpacing: "-0.045em",
        }}>
          Pequenas<br/>comemorações.
        </h1>
        <p style={{ fontSize: 17, lineHeight: 1.55, marginTop: 28, maxWidth: 480, color: "#3a363f" }}>
          Um aniversário não é uma landing page. É um ritual — convites, mesa posta, escolhas.
          Esse redesign trata o site como um <i>caderno editorial</i>: papel, tinta,
          tipografia com voz, e um único acento de cor que aparece como uma vela acesa —
          sutil, mas inconfundível.
        </p>
        <div style={{ marginTop: 40, display: "grid", gap: 14, fontSize: 14, color: "#3a363f" }}>
          {[
            ["01", "Editorial sobre genérico", "Hierarquia tipográfica forte. Grids assimétricos. Linhas, números, índices."],
            ["02", "Roxo é vela, não holofote", "Halo aparece em ações, focos e estados — nunca como fundo dominante."],
            ["03", "Motion como gesto", "Transições curtas e suaves; tudo entra deslocado para baixo, nunca aparece do nada."],
            ["04", "Texto com calor humano", "Copy em primeira pessoa, conversada. Sem 'experiência premium'."],
          ].map(([n, t, d]) => (
            <div key={n} style={{ display: "grid", gridTemplateColumns: "44px 200px 1fr", gap: 16, paddingTop: 14, borderTop: "1px solid #ECE6DA" }}>
              <span style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 11, color: "#9a8fb6" }}>{n}</span>
              <strong style={{ fontWeight: 600 }}>{t}</strong>
              <span>{d}</span>
            </div>
          ))}
        </div>
      </div>

      {/* RIGHT — system tokens */}
      <div style={{ display: "grid", gap: 36 }}>
        <div>
          <Rule>Paleta</Rule>
          <div style={{ marginTop: 18, display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
            {swatches.map((s) => (
              <div key={s.name} style={{ background: "#fff", border: "1px solid #ECE6DA", borderRadius: 4, padding: 14 }}>
                <div style={{ width: "100%", height: 64, background: s.hex, borderRadius: 2, marginBottom: 10 }}></div>
                <div style={{ display: "flex", justifyContent: "space-between", fontFamily: "'JetBrains Mono', monospace", fontSize: 11, color: "#3a363f" }}>
                  <span>{s.name}</span><span>{s.hex}</span>
                </div>
                <div style={{ fontSize: 12, color: "#7a7480", marginTop: 4 }}>{s.note}</div>
              </div>
            ))}
          </div>
        </div>

        <div>
          <Rule>Tipografia</Rule>
          <div style={{ marginTop: 18, display: "grid", gap: 14 }}>
            {types.map((t) => (
              <div key={t.name} style={{ background: "#fff", border: "1px solid #ECE6DA", borderRadius: 4, padding: 18 }}>
                <div style={{ display: "flex", justifyContent: "space-between", marginBottom: 10, fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.14em", textTransform: "uppercase", color: "#9a8fb6" }}>
                  <span>{t.name}</span>
                  <span>{t.size}/{Math.round(t.size * 1.15)}</span>
                </div>
                <div style={{
                  fontFamily: t.font, fontWeight: t.weight, fontStyle: t.italic ? "italic" : "normal",
                  fontSize: t.size, letterSpacing: t.tracking, lineHeight: 1.05, color: "#0E0E12",
                  textTransform: t.name === "Mono Caption" ? "uppercase" : "none",
                }}>{t.sample}</div>
              </div>
            ))}
          </div>
        </div>

        <div>
          <Rule>Espaço · Raio · Sombra</Rule>
          <div style={{ marginTop: 18, display: "grid", gridTemplateColumns: "1fr 1fr 1fr", gap: 12 }}>
            <div style={{ background: "#fff", border: "1px solid #ECE6DA", padding: 14, borderRadius: 4 }}>
              <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, color: "#9a8fb6", letterSpacing: "0.14em", marginBottom: 12 }}>SPACE · 4PT</div>
              <div style={{ display: "flex", alignItems: "flex-end", gap: 4 }}>
                {[4, 8, 12, 20, 32, 56].map((s) => (
                  <div key={s} style={{ width: 12, height: s, background: "#0E0E12" }}></div>
                ))}
              </div>
            </div>
            <div style={{ background: "#fff", border: "1px solid #ECE6DA", padding: 14, borderRadius: 4 }}>
              <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, color: "#9a8fb6", letterSpacing: "0.14em", marginBottom: 12 }}>RADIUS</div>
              <div style={{ display: "flex", gap: 6, alignItems: "flex-end" }}>
                {[0, 2, 4, 999].map((r) => (
                  <div key={r} style={{ width: 28, height: 28, background: "#5E2AC9", borderRadius: r }}></div>
                ))}
              </div>
              <div style={{ marginTop: 10, fontSize: 11, color: "#7a7480" }}>0 · 2 · 4 · pill</div>
            </div>
            <div style={{ background: "#fff", border: "1px solid #ECE6DA", padding: 14, borderRadius: 4 }}>
              <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, color: "#9a8fb6", letterSpacing: "0.14em", marginBottom: 12 }}>SOMBRA</div>
              <div style={{ width: 56, height: 56, background: "#fff", borderRadius: 4, boxShadow: "0 24px 40px -16px rgba(14,14,18,0.18), 0 2px 4px rgba(14,14,18,0.06)" }}></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

window.SystemCard = SystemCard;
