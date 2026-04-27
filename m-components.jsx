/* MASCULINE V2 — sci-fi techno noir, animated */

const SAMPLE_GIFTS_M = [
  { id: 1, name: "Fone Sennheiser HD 660S2",   category: "AUDIO",  price: 4290.0, qty: 1, featured: true,  desc: "Driver dinâmico de 38mm. Para escutar com atenção." },
  { id: 2, name: "Tênis Salomon XT-6 Black",   category: "GEAR",   price: 1899.0, qty: 2, featured: false, desc: "Trilha, cidade, qualquer terreno." },
  { id: 3, name: "Whisky Yamazaki 12 Anos",    category: "BAR",    price: 2890.0, qty: 1, featured: true,  desc: "Single malt japonês. Lento." },
  { id: 4, name: "Mecânico Moonswatch Mission",category: "TIME",   price: 2300.0, qty: 0, featured: false, desc: "Bioceramic. Edição Mars." },
  { id: 5, name: "Cadeira Herman Miller Aeron",category: "OFFICE", price: 8900.0, qty: 1, featured: false, desc: "Tamanho B. Carbon." },
  { id: 6, name: "Câmera Ricoh GR III x",      category: "PHOTO",  price: 7990.0, qty: 1, featured: false, desc: "APS-C. 26.1mm equivalente. No bolso." },
  { id: 7, name: "Knife Opinel No 8 Carbono",  category: "EDC",    price: 180.0,  qty: 4, featured: false, desc: "Cabo nogueira. Lâmina de carbono." },
  { id: 8, name: "Notebook Leuchtturm 1917",   category: "DESK",   price: 220.0,  qty: 6, featured: false, desc: "A5, ponto, preto fosco." },
];

function MTag({ children, kind = "default", style }) {
  const palette = {
    default: { bg: "rgba(255,255,255,0.04)", color: "#a3a3b0", border: "rgba(255,255,255,0.12)" },
    accent:  { bg: "rgba(0,255,180,0.08)",   color: "#00FFB4", border: "rgba(0,255,180,0.32)" },
    danger:  { bg: "rgba(255,80,80,0.10)",   color: "#ff5050", border: "rgba(255,80,80,0.32)" },
    cyan:    { bg: "rgba(0,200,255,0.08)",   color: "#00C8FF", border: "rgba(0,200,255,0.32)" },
  };
  const p = palette[kind] || palette.default;
  return (
    <span style={{
      display: "inline-flex", alignItems: "center", gap: 6,
      fontFamily: "'JetBrains Mono', ui-monospace, monospace",
      fontSize: 10, letterSpacing: "0.2em", textTransform: "uppercase",
      padding: "5px 10px", borderRadius: 2,
      background: p.bg, color: p.color, border: `1px solid ${p.border}`,
      ...style,
    }}>{children}</span>
  );
}

/* Animated counter that increments visibly */
function ScrambleText({ children, trigger = 0 }) {
  const [text, setText] = React.useState(children);
  React.useEffect(() => {
    const target = String(children);
    const chars = "▓▒░█01ABCDEF#%&";
    let i = 0;
    const id = setInterval(() => {
      const out = target.split("").map((c, j) => {
        if (j < i) return target[j];
        if (c === " ") return " ";
        return chars[Math.floor(Math.random() * chars.length)];
      }).join("");
      setText(out);
      i += 1;
      if (i > target.length) clearInterval(id);
    }, 38);
    return () => clearInterval(id);
  }, [children, trigger]);
  return <>{text}</>;
}

/* Animated gift visual — rotating wireframe */
function GiftCubeM({ label, hue = 180, animate = true }) {
  return (
    <div style={{
      position: "relative", aspectRatio: "4/5", width: "100%",
      background: "#0a0a0d",
      border: "1px solid rgba(255,255,255,0.08)",
      overflow: "hidden",
    }}>
      {/* grid bg */}
      <div style={{
        position: "absolute", inset: 0,
        backgroundImage: `linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px)`,
        backgroundSize: "24px 24px",
      }}></div>

      {/* glow orb */}
      <div style={{
        position: "absolute", inset: "20% 25%",
        background: `radial-gradient(circle, hsla(${hue}, 100%, 60%, 0.35), transparent 60%)`,
        filter: "blur(18px)",
        animation: animate ? "pulseM 3.6s ease-in-out infinite" : "none",
      }}></div>

      {/* rotating square */}
      <div style={{
        position: "absolute", left: "50%", top: "50%",
        transform: "translate(-50%, -50%)",
        width: "55%", aspectRatio: "1",
        animation: animate ? "spinM 22s linear infinite" : "none",
      }}>
        <div style={{
          position: "absolute", inset: 0,
          border: `1px solid hsla(${hue},100%,70%,0.7)`,
          transform: "rotate(0deg)",
        }}></div>
        <div style={{
          position: "absolute", inset: 0,
          border: `1px solid hsla(${hue},100%,70%,0.4)`,
          transform: "rotate(45deg)",
        }}></div>
        <div style={{
          position: "absolute", inset: "20%",
          border: `1px solid hsla(${hue},100%,70%,0.5)`,
        }}></div>
      </div>

      {/* monogram */}
      <div style={{
        position: "absolute", inset: 0,
        display: "flex", alignItems: "center", justifyContent: "center",
        fontFamily: "'JetBrains Mono', monospace",
        fontSize: 96, fontWeight: 500, color: "#fff",
        letterSpacing: "-0.05em",
        textShadow: `0 0 28px hsla(${hue}, 100%, 60%, 0.6)`,
        zIndex: 2,
      }}>{label}</div>

      {/* corner ticks */}
      {[
        { left: 8, top: 8 },     { right: 8, top: 8 },
        { left: 8, bottom: 8 },  { right: 8, bottom: 8 },
      ].map((p, i) => (
        <div key={i} style={{
          position: "absolute", ...p, width: 14, height: 14,
          borderTop: i < 2 ? "1px solid rgba(255,255,255,0.4)" : "none",
          borderBottom: i >= 2 ? "1px solid rgba(255,255,255,0.4)" : "none",
          borderLeft: (i === 0 || i === 2) ? "1px solid rgba(255,255,255,0.4)" : "none",
          borderRight: (i === 1 || i === 3) ? "1px solid rgba(255,255,255,0.4)" : "none",
        }}></div>
      ))}

      {/* meta */}
      <div style={{
        position: "absolute", left: 14, top: 14,
        fontFamily: "'JetBrains Mono', monospace", fontSize: 9,
        letterSpacing: "0.18em", color: "rgba(255,255,255,0.5)",
      }}>ID-{String(label.charCodeAt(0)).padStart(3, "0")}</div>
      <div style={{
        position: "absolute", right: 14, top: 14,
        fontFamily: "'JetBrains Mono', monospace", fontSize: 9,
        letterSpacing: "0.18em", color: `hsla(${hue},100%,70%,0.9)`,
      }}>● ONLINE</div>

      {/* scanline */}
      {animate && (
        <div style={{
          position: "absolute", left: 0, right: 0, height: 2,
          background: `linear-gradient(90deg, transparent, hsla(${hue},100%,70%,0.7), transparent)`,
          animation: "scanM 4s ease-in-out infinite",
          pointerEvents: "none",
        }}></div>
      )}
    </div>
  );
}

function brlM(n) {
  return n.toLocaleString("pt-BR", { style: "currency", currency: "BRL", maximumFractionDigits: 0 });
}

Object.assign(window, { SAMPLE_GIFTS_M, MTag, ScrambleText, GiftCubeM, brlM });
