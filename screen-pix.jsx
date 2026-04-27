/* PIX SCREEN — two columns, editorial */

function PixScreen() {
  const [amount, setAmount] = React.useState("150,00");
  const [copied, setCopied] = React.useState(false);
  const suggestions = [50, 100, 150, 250, 500];

  return (
    <div style={{
      width: 1280, background: "#F6F2EA", color: "#0E0E12",
      fontFamily: "'Söhne','Inter',sans-serif",
    }}>
      <header style={{ padding: "20px 56px", borderBottom: "1px solid #ECE6DA", display: "flex", justifyContent: "space-between", alignItems: "center" }}>
        <div style={{ fontFamily: "'Fraunces', serif", fontStyle: "italic", fontWeight: 300, fontSize: 22 }}>
          aniversários — <span style={{ color: "#5E2AC9" }}>Mariana ·30</span>
        </div>
        <nav style={{ display: "flex", gap: 26, fontSize: 14 }}>
          {["Início", "Presentes", "Pix", "Meus presentes"].map((l) => (
            <a key={l} href="#" style={{
              color: l === "Pix" ? "#0E0E12" : "#3a363f", textDecoration: "none",
              fontWeight: l === "Pix" ? 600 : 400,
              borderBottom: l === "Pix" ? "1px solid #0E0E12" : "1px solid transparent", paddingBottom: 2,
            }}>{l}</a>
          ))}
        </nav>
      </header>

      <section style={{ padding: "56px 56px 24px" }}>
        <Tag style={{ marginBottom: 16 }}>capítulo 03 · contribuir</Tag>
        <h1 style={{
          fontFamily: "'Fraunces', serif", fontWeight: 300,
          fontSize: 96, margin: 0, letterSpacing: "-0.045em", lineHeight: 0.95, maxWidth: 900,
        }}>
          Não <i>precisa</i> escolher.<br/>Pode contribuir um pouquinho.
        </h1>
        <p style={{ fontSize: 16, color: "#3a363f", maxWidth: 620, marginTop: 24, lineHeight: 1.55 }}>
          Se você prefere mandar um valor — qualquer valor — eu junto com outras
          contribuições e cuido dos presentes da lista. É o jeito mais leve de participar.
        </p>
      </section>

      <section style={{ padding: "32px 56px 80px", display: "grid", gridTemplateColumns: "1fr 1.05fr", gap: 28, alignItems: "stretch" }}>
        {/* LEFT — payment data + QR */}
        <div style={{
          background: "#0E0E12", color: "#F6F2EA", borderRadius: 4,
          padding: 36, position: "relative", overflow: "hidden",
        }}>
          <div style={{ display: "flex", justifyContent: "space-between", marginBottom: 32, fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.18em", color: "#9a8fb6", textTransform: "uppercase" }}>
            <span>Dados Pix · BR</span>
            <span>secured · ssl</span>
          </div>

          <div style={{ display: "grid", gridTemplateColumns: "1fr 220px", gap: 24, alignItems: "start" }}>
            <div>
              <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.16em", color: "#9a8fb6", marginBottom: 6, textTransform: "uppercase" }}>Recebedor</div>
              <div style={{ fontFamily: "'Fraunces', serif", fontWeight: 300, fontSize: 32, marginBottom: 24 }}>Mariana C. de Souza</div>

              <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.16em", color: "#9a8fb6", marginBottom: 6, textTransform: "uppercase" }}>Chave</div>
              <div style={{ fontSize: 18, fontWeight: 500, marginBottom: 24, fontFamily: "'JetBrains Mono', monospace", letterSpacing: "0.04em" }}>mariana.aniversario@gmail.com</div>

              <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.16em", color: "#9a8fb6", marginBottom: 6, textTransform: "uppercase" }}>Cidade</div>
              <div style={{ fontSize: 14 }}>Olinda — PE</div>
            </div>

            {/* QR */}
            <div style={{
              background: "#F6F2EA", borderRadius: 4, padding: 14,
              aspectRatio: "1", display: "grid",
              gridTemplateColumns: "repeat(12, 1fr)", gridTemplateRows: "repeat(12, 1fr)", gap: 1,
            }}>
              {Array.from({ length: 144 }).map((_, i) => {
                // pseudo-random pattern
                const on = (i * 31 + (i % 7) * 13) % 5 < 2;
                const corner = (i < 36 && (i % 12) < 3) || (i < 36 && (i % 12) >= 9) || (i >= 108 && (i % 12) < 3);
                return (
                  <div key={i} style={{ background: corner || on ? "#0E0E12" : "transparent" }}></div>
                );
              })}
            </div>
          </div>

          <div style={{ marginTop: 32, paddingTop: 24, borderTop: "1px solid rgba(255,255,255,0.12)" }}>
            <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.16em", color: "#9a8fb6", marginBottom: 10, textTransform: "uppercase" }}>Pix copia e cola</div>
            <div style={{
              fontFamily: "'JetBrains Mono', monospace", fontSize: 11, lineHeight: 1.5,
              background: "rgba(255,255,255,0.04)", border: "1px solid rgba(255,255,255,0.08)",
              padding: 14, borderRadius: 2, color: "#e9defe", wordBreak: "break-all",
            }}>
              00020126360014BR.GOV.BCB.PIX0114mariana.aniv...520400005303986540515.005802BR5921MARIANA C DE SOUZA6008OLINDA62070503***6304A8B2
            </div>
            <button
              onClick={() => { setCopied(true); setTimeout(() => setCopied(false), 1500); }}
              style={{
                marginTop: 12, background: copied ? "#5E2AC9" : "#F6F2EA",
                color: copied ? "#F6F2EA" : "#0E0E12",
                border: "none", borderRadius: 999, padding: "12px 22px",
                fontSize: 13, fontWeight: 600, cursor: "pointer",
                transition: "all 200ms ease",
              }}>
              {copied ? "✓ copiado" : "Copiar código"}
            </button>
          </div>
        </div>

        {/* RIGHT — contribution form */}
        <div style={{ background: "#fff", border: "1px solid #ECE6DA", borderRadius: 4, padding: 36 }}>
          <Tag kind="soft" style={{ marginBottom: 18 }}>★ Você está contribuindo para: Cafeteira Bialetti</Tag>

          <h2 style={{ fontFamily: "'Fraunces', serif", fontWeight: 300, fontSize: 36, margin: "0 0 24px", letterSpacing: "-0.02em" }}>
            Quanto você quer mandar?
          </h2>

          {/* Amount input — huge */}
          <div style={{ position: "relative", marginBottom: 16, paddingBottom: 16, borderBottom: "1px solid #ECE6DA" }}>
            <span style={{
              position: "absolute", left: 0, top: 16,
              fontFamily: "'Fraunces', serif", fontWeight: 300, fontSize: 56,
              color: "#9a8fb6",
            }}>R$</span>
            <input
              value={amount} onChange={(e) => setAmount(e.target.value)}
              style={{
                width: "100%", border: "none", outline: "none",
                fontFamily: "'Fraunces', serif", fontWeight: 300,
                fontSize: 96, letterSpacing: "-0.04em",
                background: "transparent", color: "#0E0E12",
                padding: "0 0 0 90px", lineHeight: 1,
              }}
            />
          </div>

          <div style={{ display: "flex", gap: 8, flexWrap: "wrap", marginBottom: 28 }}>
            {suggestions.map((v) => (
              <button key={v} onClick={() => setAmount(v.toFixed(2).replace(".", ","))} style={{
                background: amount === v.toFixed(2).replace(".", ",") ? "#0E0E12" : "transparent",
                color: amount === v.toFixed(2).replace(".", ",") ? "#F6F2EA" : "#3a363f",
                border: "1px solid rgba(14,14,18,0.16)", borderRadius: 999,
                padding: "8px 16px", fontSize: 13, fontWeight: 500, cursor: "pointer",
              }}>R$ {v}</button>
            ))}
          </div>

          <div style={{ display: "grid", gap: 14 }}>
            <div>
              <label style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.16em", textTransform: "uppercase", color: "#9a8fb6" }}>Seu nome</label>
              <input defaultValue="João Pedro Lima" style={{
                width: "100%", marginTop: 6, padding: "12px 0",
                border: "none", borderBottom: "1px solid #ECE6DA",
                background: "transparent", fontSize: 16, fontFamily: "inherit", outline: "none",
              }} />
            </div>
            <div>
              <label style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.16em", textTransform: "uppercase", color: "#9a8fb6" }}>E-mail</label>
              <input defaultValue="joao@exemplo.com" style={{
                width: "100%", marginTop: 6, padding: "12px 0",
                border: "none", borderBottom: "1px solid #ECE6DA",
                background: "transparent", fontSize: 16, fontFamily: "inherit", outline: "none",
              }} />
            </div>
            <div>
              <label style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.16em", textTransform: "uppercase", color: "#9a8fb6" }}>Mensagem (opcional)</label>
              <textarea placeholder="Parabéns, Mari! Que esse café te acompanhe…" rows={2} style={{
                width: "100%", marginTop: 6, padding: "12px 0",
                border: "none", borderBottom: "1px solid #ECE6DA",
                background: "transparent", fontSize: 15, fontFamily: "inherit",
                resize: "none", outline: "none",
              }}></textarea>
            </div>
          </div>

          <button style={{
            marginTop: 28, width: "100%",
            background: "#0E0E12", color: "#F6F2EA",
            border: "none", borderRadius: 999,
            padding: "18px", fontSize: 15, fontWeight: 600, cursor: "pointer",
            display: "inline-flex", alignItems: "center", justifyContent: "center", gap: 10,
          }}>
            Confirmar contribuição
            <span style={{ fontFamily: "'Fraunces', serif", fontStyle: "italic", fontWeight: 300 }}>→</span>
          </button>

          <div style={{ marginTop: 16, fontSize: 12, color: "#7a7480", textAlign: "center" }}>
            Você verá o pagamento aparecer aqui assim que for compensado. Sem cadastro, sem login.
          </div>
        </div>
      </section>
    </div>
  );
}

window.PixScreen = PixScreen;
