/* HOME — editorial hero with countdown, runner, mosaic */

function HomeScreen() {
  const [days, setDays] = React.useState(47);
  const [hours, setHours] = React.useState(8);
  const [mins, setMins] = React.useState(23);
  const [secs, setSecs] = React.useState(11);

  React.useEffect(() => {
    const t = setInterval(() => {
      setSecs((s) => (s === 0 ? 59 : s - 1));
    }, 1000);
    return () => clearInterval(t);
  }, []);

  const featured = SAMPLE_GIFTS.slice(0, 3);

  return (
    <div style={{
      width: 1280, background: "#F6F2EA", color: "#0E0E12",
      fontFamily: "'Söhne','Inter',sans-serif", fontFeatureSettings: "'ss01'",
    }}>
      {/* HEADER */}
      <header style={{
        display: "grid", gridTemplateColumns: "1fr auto 1fr",
        alignItems: "center", padding: "22px 56px",
        borderBottom: "1px solid #ECE6DA",
      }}>
        <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 11, letterSpacing: "0.18em", textTransform: "uppercase", color: "#7a7480" }}>
          Olinda · BR &nbsp;·&nbsp; Anos {new Date().getFullYear()}
        </div>
        <div style={{
          fontFamily: "'Fraunces', serif", fontWeight: 300, fontStyle: "italic",
          fontSize: 22, letterSpacing: "-0.02em",
        }}>aniversários — <span style={{ color: "#5E2AC9" }}>Mariana ·30</span></div>
        <nav style={{ display: "flex", gap: 26, justifyContent: "flex-end", fontSize: 14 }}>
          {["Início", "Presentes", "Pix", "Meus presentes"].map((l, i) => (
            <a key={l} href="#" style={{
              color: i === 0 ? "#0E0E12" : "#3a363f", textDecoration: "none",
              fontWeight: i === 0 ? 600 : 400,
              borderBottom: i === 0 ? "1px solid #0E0E12" : "1px solid transparent",
              paddingBottom: 2,
            }}>{l}</a>
          ))}
        </nav>
      </header>

      {/* HERO */}
      <section style={{ padding: "72px 56px 24px", display: "grid", gridTemplateColumns: "1.4fr 1fr", gap: 56 }}>
        <div>
          <Tag kind="default" style={{ marginBottom: 24 }}>★ Convite aberto · 12 de junho</Tag>
          <h1 style={{
            fontFamily: "'Fraunces', serif", fontWeight: 300,
            fontSize: 132, lineHeight: 0.92, margin: 0, letterSpacing: "-0.05em",
            color: "#0E0E12",
          }}>
            Para a<br/>
            <i>Mariana,</i><br/>
            que faz 30.
          </h1>
          <p style={{ fontSize: 18, lineHeight: 1.55, marginTop: 32, maxWidth: 480, color: "#3a363f" }}>
            Não é uma lista de presentes — é um caderno de coisas pequenas e
            específicas que ela vem mencionando desde janeiro. Escolha uma. Ou contribua um pouco
            via Pix, sem precisar escolher nada.
          </p>
          <div style={{ display: "flex", gap: 14, marginTop: 36 }}>
            <button style={{
              background: "#0E0E12", color: "#F6F2EA", border: "none",
              padding: "16px 28px", fontSize: 14, fontWeight: 600,
              borderRadius: 999, cursor: "pointer", letterSpacing: "0.01em",
              display: "inline-flex", alignItems: "center", gap: 10,
            }}>
              Ver os presentes
              <span style={{ fontFamily: "'Fraunces', serif", fontStyle: "italic", fontWeight: 300 }}>→</span>
            </button>
            <button style={{
              background: "transparent", color: "#0E0E12",
              border: "1px solid rgba(14,14,18,0.2)",
              padding: "16px 28px", fontSize: 14, fontWeight: 600,
              borderRadius: 999, cursor: "pointer",
            }}>
              Contribuir via Pix
            </button>
          </div>
        </div>

        {/* COUNTDOWN — vertical stack of huge digits */}
        <div style={{
          background: "#0E0E12", color: "#F6F2EA", borderRadius: 4,
          padding: 36, position: "relative", overflow: "hidden",
        }}>
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start" }}>
            <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.18em", textTransform: "uppercase", color: "#9a8fb6" }}>
              Faltam exatamente
            </div>
            <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.18em", color: "#9a8fb6" }}>
              CTDN · 047
            </div>
          </div>
          <div style={{
            fontFamily: "'Fraunces', serif", fontWeight: 200, fontStyle: "italic",
            fontSize: 220, lineHeight: 0.85, letterSpacing: "-0.06em",
            marginTop: 22, color: "#F6F2EA",
            display: "flex", alignItems: "flex-end", gap: 0,
          }}>
            {String(days).padStart(2, "0")}
            <span style={{ fontSize: 64, color: "#5E2AC9", marginLeft: 8, marginBottom: 18 }}>·</span>
          </div>
          <div style={{ fontSize: 13, marginTop: 4, color: "#c6becf" }}>dias até 12 de junho</div>

          <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 0, marginTop: 36, borderTop: "1px solid rgba(255,255,255,0.12)", paddingTop: 18 }}>
            {[
              ["horas", String(hours).padStart(2, "0")],
              ["min",   String(mins).padStart(2, "0")],
              ["seg",   String(secs).padStart(2, "0")],
            ].map(([l, v]) => (
              <div key={l}>
                <div style={{ fontFamily: "'Fraunces', serif", fontWeight: 300, fontSize: 38, letterSpacing: "-0.03em" }}>{v}</div>
                <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 9, letterSpacing: "0.18em", textTransform: "uppercase", color: "#9a8fb6" }}>{l}</div>
              </div>
            ))}
          </div>

          {/* halo orb */}
          <div style={{
            position: "absolute", right: -80, bottom: -80, width: 220, height: 220,
            background: "radial-gradient(circle, rgba(94,42,201,0.6), transparent 70%)",
            borderRadius: 999, filter: "blur(20px)", pointerEvents: "none",
          }}></div>
        </div>
      </section>

      {/* MARQUEE */}
      <div style={{
        borderTop: "1px solid #ECE6DA", borderBottom: "1px solid #ECE6DA",
        padding: "18px 0", overflow: "hidden", whiteSpace: "nowrap",
        fontFamily: "'Fraunces', serif", fontStyle: "italic", fontWeight: 300,
        fontSize: 32, letterSpacing: "-0.02em",
      }}>
        <div style={{ display: "inline-block", animation: "marqueeRun 38s linear infinite" }}>
          {Array.from({ length: 4 }).map((_, i) => (
            <span key={i} style={{ marginRight: 56 }}>
              cafeteira italiana <span style={{ color: "#5E2AC9" }}>★</span>{" "}
              vinil tim maia <span style={{ color: "#5E2AC9" }}>★</span>{" "}
              câmera instantânea <span style={{ color: "#5E2AC9" }}>★</span>{" "}
              taças riedel <span style={{ color: "#5E2AC9" }}>★</span>{" "}
              livro de árvores <span style={{ color: "#5E2AC9" }}>★</span>{" "}
            </span>
          ))}
        </div>
      </div>

      {/* MOSAIC — three featured */}
      <section style={{ padding: "72px 56px" }}>
        <div style={{ display: "flex", alignItems: "flex-end", justifyContent: "space-between", marginBottom: 36 }}>
          <div>
            <Tag>capítulo 01 · em destaque</Tag>
            <h2 style={{
              fontFamily: "'Fraunces', serif", fontWeight: 300,
              fontSize: 56, margin: "12px 0 0", letterSpacing: "-0.03em",
            }}>O que ela <i>quer mesmo.</i></h2>
          </div>
          <a href="#" style={{ color: "#0E0E12", fontSize: 14, fontWeight: 600, textDecoration: "none", borderBottom: "1px solid #0E0E12", paddingBottom: 2 }}>
            Ver os 24 presentes →
          </a>
        </div>

        <div style={{ display: "grid", gridTemplateColumns: "1.2fr 1fr 1fr", gap: 24, alignItems: "stretch" }}>
          {featured.map((g, i) => (
            <article key={g.id} style={{
              background: "#fff", border: "1px solid #ECE6DA",
              borderRadius: 4, padding: 18,
              transform: i === 1 ? "translateY(40px)" : "none",
            }}>
              <GiftVisual tone={g.tone} label={g.name[0]} ratio={i === 0 ? "5/6" : "4/5"} />
              <div style={{ display: "flex", justifyContent: "space-between", marginTop: 18, fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.16em", textTransform: "uppercase", color: "#9a8fb6" }}>
                <span>N° {String(g.id).padStart(2, "0")} · {g.category}</span>
                <span>{g.qty} disp.</span>
              </div>
              <h3 style={{
                fontFamily: "'Fraunces', serif", fontWeight: 400,
                fontSize: i === 0 ? 32 : 24, margin: "10px 0 8px",
                letterSpacing: "-0.02em", lineHeight: 1.05,
              }}>{g.name}</h3>
              <p style={{ fontSize: 14, color: "#3a363f", margin: 0, lineHeight: 1.5 }}>{g.desc}</p>
              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginTop: 24 }}>
                <span style={{ fontFamily: "'Fraunces', serif", fontSize: 22, fontWeight: 400 }}>{brl(g.price)}</span>
                <button style={{
                  background: "transparent", color: "#0E0E12",
                  border: "1px solid #0E0E12", borderRadius: 999,
                  padding: "10px 18px", fontSize: 13, fontWeight: 600, cursor: "pointer",
                }}>Escolher →</button>
              </div>
            </article>
          ))}
        </div>
      </section>

      {/* FOOTER */}
      <footer style={{
        borderTop: "1px solid #ECE6DA", padding: "36px 56px 56px",
        display: "grid", gridTemplateColumns: "2fr 1fr 1fr", gap: 32, fontSize: 13, color: "#3a363f",
      }}>
        <div>
          <div style={{ fontFamily: "'Fraunces', serif", fontStyle: "italic", fontWeight: 300, fontSize: 22, marginBottom: 8 }}>
            obrigada por vir até aqui.
          </div>
          <div style={{ color: "#7a7480", maxWidth: 360 }}>
            Se for em dúvida sobre algo da lista, pode me mandar mensagem que eu te ajudo.
          </div>
        </div>
        <div>
          <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.16em", textTransform: "uppercase", color: "#9a8fb6", marginBottom: 10 }}>Mapa</div>
          <div>Início</div>
          <div>Presentes</div>
          <div>Pix</div>
          <div>Meus presentes</div>
        </div>
        <div style={{ textAlign: "right" }}>
          <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.16em", color: "#9a8fb6" }}>
            v.2 · jun/2026
          </div>
        </div>
      </footer>

      <style>{`
        @keyframes marqueeRun {
          from { transform: translateX(0); }
          to   { transform: translateX(-50%); }
        }
      `}</style>
    </div>
  );
}

window.HomeScreen = HomeScreen;
