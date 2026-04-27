/* Shared primitives + sample data for the Aniversário redesign */

const SAMPLE_GIFTS = [
  { id: 1, name: "Cafeteira Italiana Bialetti", category: "Cozinha", price: 289.9, qty: 2, featured: true,  desc: "Para começar manhãs com calma e cheiro de café passado na hora.", tone: "rose" },
  { id: 2, name: "Livro: A vida secreta das árvores", category: "Livros", price: 64.0, qty: 1, featured: false, desc: "Um presente de leitura lenta, para fins de tarde longas.", tone: "stone" },
  { id: 3, name: "Vinil — Tim Maia, Racional", category: "Música", price: 320.0, qty: 1, featured: true,  desc: "Para a vitrola, para a sala, para os domingos de cozinhar.", tone: "violet" },
  { id: 4, name: "Conjunto de Taças Riedel", category: "Cozinha", price: 540.0, qty: 0, featured: false, desc: "Uma jantar inteira ganha outro tom.", tone: "wine" },
  { id: 5, name: "Câmera Instantânea Mini 12", category: "Memória", price: 599.0, qty: 3, featured: false, desc: "Fotos físicas para colar na geladeira.", tone: "sky" },
  { id: 6, name: "Plantador de cerâmica Olivia", category: "Casa", price: 180.0, qty: 4, featured: false, desc: "Feito à mão por um ateliê de Olinda.", tone: "moss" },
];

const TONE_BG = {
  rose:   "linear-gradient(160deg, #f3dcd4, #d9b8ad)",
  stone:  "linear-gradient(160deg, #e9e3d9, #c9bfae)",
  violet: "linear-gradient(160deg, #d6cdf2, #6c4ad8)",
  wine:   "linear-gradient(160deg, #c2a0a3, #5a1a25)",
  sky:    "linear-gradient(160deg, #d6e3ee, #97aebf)",
  moss:   "linear-gradient(160deg, #d3dcc7, #7c8a68)",
};

/* Format BRL */
const brl = (n) =>
  n.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });

/* Tag pill — small uppercase label */
function Tag({ children, kind = "default", style }) {
  const palette = {
    default: { bg: "rgba(20,20,24,0.06)", color: "#14141a", border: "rgba(20,20,24,0.10)" },
    accent:  { bg: "#1a0b3d", color: "#e9defe", border: "transparent" },
    soft:    { bg: "#f3eefe", color: "#3b1d8a", border: "rgba(94,42,201,0.18)" },
    out:     { bg: "transparent", color: "#9a8fb6", border: "rgba(20,20,24,0.16)" },
  };
  const p = palette[kind] || palette.default;
  return (
    <span style={{
      display: "inline-flex", alignItems: "center", gap: 6,
      fontFamily: "'JetBrains Mono', ui-monospace, monospace",
      fontSize: 10, letterSpacing: "0.14em", textTransform: "uppercase",
      padding: "5px 9px", borderRadius: 999,
      background: p.bg, color: p.color, border: `1px solid ${p.border}`,
      ...style,
    }}>
      {children}
    </span>
  );
}

/* Editorial divider with caption */
function Rule({ children }) {
  return (
    <div style={{ display: "flex", alignItems: "center", gap: 14, color: "#7a7480" }}>
      <span style={{ flex: 1, height: 1, background: "rgba(20,20,24,0.14)" }}></span>
      <span style={{
        fontFamily: "'JetBrains Mono', monospace",
        fontSize: 10, letterSpacing: "0.18em", textTransform: "uppercase",
      }}>{children}</span>
      <span style={{ flex: 1, height: 1, background: "rgba(20,20,24,0.14)" }}></span>
    </div>
  );
}

/* Placeholder gift visual: textured gradient + monogram letter */
function GiftVisual({ tone = "stone", label, ratio = "4 / 5", style }) {
  return (
    <div style={{
      position: "relative", aspectRatio: ratio, width: "100%",
      background: TONE_BG[tone] || TONE_BG.stone,
      borderRadius: 4, overflow: "hidden",
      ...style,
    }}>
      {/* film grain */}
      <div style={{
        position: "absolute", inset: 0,
        backgroundImage:
          "radial-gradient(rgba(255,255,255,0.18) 1px, transparent 1px), radial-gradient(rgba(0,0,0,0.10) 1px, transparent 1px)",
        backgroundSize: "3px 3px, 5px 5px",
        backgroundPosition: "0 0, 1px 2px",
        mixBlendMode: "overlay", opacity: 0.7,
      }}></div>
      {/* monogram */}
      <div style={{
        position: "absolute", inset: 0,
        display: "flex", alignItems: "center", justifyContent: "center",
        fontFamily: "'Fraunces', 'Times New Roman', serif",
        fontWeight: 300, fontStyle: "italic",
        fontSize: "min(40%, 180px)", color: "rgba(255,255,255,0.85)",
        letterSpacing: "-0.04em",
        textShadow: "0 4px 20px rgba(0,0,0,0.18)",
      }}>{label}</div>
      {/* corner index */}
      <div style={{
        position: "absolute", left: 12, top: 12,
        fontFamily: "'JetBrains Mono', monospace",
        fontSize: 10, color: "rgba(255,255,255,0.7)",
        letterSpacing: "0.16em",
      }}>N° {String((label || "X").charCodeAt(0) % 100).padStart(2, "0")}</div>
    </div>
  );
}

Object.assign(window, { SAMPLE_GIFTS, TONE_BG, brl, Tag, Rule, GiftVisual });
