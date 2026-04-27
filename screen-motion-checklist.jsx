/* MOTION GUIDE + IMPLEMENTATION CHECKLIST + COPY */

function MotionCard() {
  const tracks = [
    { name: "Hero entry",          dur: "640ms", easing: "cubic-bezier(.2,.8,.2,1)", desc: "Título sobe 24px com fade. Stagger de 80ms entre linhas.", phases: 3 },
    { name: "Countdown digits",    dur: "1000ms × loop", easing: "ease-out", desc: "Cada dígito faz flip vertical no minuto/segundo. Halo pulsa a cada 8s.", phases: 4 },
    { name: "Card hover",          dur: "320ms", easing: "cubic-bezier(.2,.8,.2,1)", desc: "Y −6px, sombra cresce, número de série fica roxo.", phases: 1 },
    { name: "Filtro / sort",       dur: "280ms", easing: "ease-out", desc: "FLIP layout: cards desaparecem com scale .96 + fade, reentram em 60ms stagger.", phases: 2 },
    { name: "Modal auth",          dur: "420ms abre · 240ms fecha", easing: "cubic-bezier(.16,1,.3,1) · ease-in", desc: "Fundo blur sobe junto. Painel lateral preto entra de baixo +24px.", phases: 2 },
    { name: "Botão press",         dur: "120ms", easing: "ease-out", desc: "Scale .98 + ring de halo (1px roxo). Liberação em spring leve.", phases: 1 },
    { name: "Reserva sucesso",     dur: "900ms", easing: "ease-in-out", desc: "Card flipa para verso, mostra ✓ desenhado em path 320ms, toast sobe.", phases: 3 },
    { name: "Pix copiar código",   dur: "1500ms", easing: "ease-out", desc: "Botão muda para roxo, label vira '✓ copiado', volta após 1.5s.", phases: 2 },
  ];

  return (
    <div style={{ background: "#0E0E12", color: "#F6F2EA", padding: 56, fontFamily: "'Söhne','Inter',sans-serif" }}>
      <Tag kind="default" style={{ background: "rgba(255,255,255,0.08)", color: "#c6becf", border: "none", marginBottom: 18 }}>
        capítulo 05 · motion
      </Tag>
      <h2 style={{ fontFamily: "'Fraunces', serif", fontWeight: 300, fontSize: 72, margin: 0, letterSpacing: "-0.045em", lineHeight: 0.95 }}>
        Motion como <i>gesto</i> — não como peça.
      </h2>
      <p style={{ fontSize: 15, color: "#c6becf", marginTop: 18, maxWidth: 720, lineHeight: 1.55 }}>
        Toda animação é curta (≤ 640ms na maioria), com easing personalizada. Tudo respeita
        <code style={{ background: "rgba(255,255,255,0.08)", padding: "2px 6px", borderRadius: 2, margin: "0 4px", fontSize: 13 }}>prefers-reduced-motion: reduce</code>
        — quando ativo, transições caem para 0ms e mantêm apenas opacity.
      </p>

      <div style={{ marginTop: 40, display: "grid", gridTemplateColumns: "1fr 1fr", gap: 14 }}>
        {tracks.map((t) => (
          <div key={t.name} style={{
            border: "1px solid rgba(255,255,255,0.12)", borderRadius: 4, padding: 22,
            background: "rgba(255,255,255,0.02)",
          }}>
            <div style={{ display: "flex", justifyContent: "space-between", marginBottom: 10 }}>
              <span style={{ fontFamily: "'Fraunces', serif", fontWeight: 400, fontSize: 20, letterSpacing: "-0.01em" }}>{t.name}</span>
              <span style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.14em", color: "#9a8fb6", textTransform: "uppercase" }}>{t.dur}</span>
            </div>
            <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 11, color: "#9a8fb6", marginBottom: 12, letterSpacing: "0.04em" }}>
              {t.easing}
            </div>
            <div style={{ display: "flex", gap: 4, marginBottom: 12 }}>
              {Array.from({ length: t.phases }).map((_, i) => (
                <div key={i} style={{ flex: 1, height: 4, background: "#5E2AC9", opacity: 1 - i * 0.18, borderRadius: 2 }}></div>
              ))}
            </div>
            <p style={{ fontSize: 13, color: "#e9defe", margin: 0, lineHeight: 1.5 }}>{t.desc}</p>
          </div>
        ))}
      </div>

      {/* CSS snippet */}
      <div style={{ marginTop: 36, border: "1px solid rgba(255,255,255,0.12)", borderRadius: 4, padding: 24, background: "#1a1a22" }}>
        <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.16em", color: "#9a8fb6", textTransform: "uppercase", marginBottom: 14 }}>
          tokens · :root + reduced-motion
        </div>
        <pre style={{
          fontFamily: "'JetBrains Mono', monospace", fontSize: 12, lineHeight: 1.6,
          margin: 0, color: "#e9defe", whiteSpace: "pre-wrap",
        }}>{`:root {
  --ease-out-soft:   cubic-bezier(0.2, 0.8, 0.2, 1);
  --ease-out-spring: cubic-bezier(0.16, 1, 0.3, 1);
  --dur-1: 120ms;   /* tap */
  --dur-2: 240ms;   /* hover, fade */
  --dur-3: 420ms;   /* modal, page */
  --dur-4: 640ms;   /* hero entry */
}

@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}`}</pre>
      </div>
    </div>
  );
}

/* IMPLEMENTATION CHECKLIST */
function ImplementationCard() {
  const sections = [
    {
      title: "Tokens CSS",
      items: [
        "Substituir variáveis em :root no app.css (cores, raios, sombras, durações)",
        "Adicionar fonts: Fraunces (300, 400 italic), Söhne ou Inter (400/600/700), JetBrains Mono (500)",
        "Trocar background body para #F6F2EA, remover bg-orb / gradient blobs roxos",
      ],
    },
    {
      title: "Layout principal (layouts/main.php)",
      items: [
        "Header em grid 3 colunas (cidade · marca · nav) — remover backdrop-filter",
        "Marca tipográfica 'aniversários — <Nome ·idade>' em Fraunces italic",
        "Nav com underline indicador (não pill roxo)",
        "Footer com 'capítulos' em vez de SEO blocks",
      ],
    },
    {
      title: "Home (public/home/index.php)",
      items: [
        "Hero 1.4fr / 1fr · h1 em Fraunces 132px italic com line-break manual",
        "Countdown card preto: dígito grande Fraunces, halo radial atrás",
        "Marquee CSS infinito (animation-marquee 38s linear)",
        "Mosaic = grid 3 cols com offset Y no card central (transform: translateY(40px))",
      ],
    },
    {
      title: "Lista (public/gifts/_cards.php + index.php)",
      items: [
        "Trocar bootstrap col-* por grid 12 cols, span variável [5,4,3,4,4,4]",
        "Adicionar offset vertical nos cards: marginTop alternado [0,60,24,0,40,12]",
        "Estado esgotado: tag preta 'já reservado', opacity .55, grayscale .4",
        "Estado destaque: pill ★ destaque em outline roxo (não amarelo bg-warning)",
        "Filter bar: input com ⌕ icon, segmented control de categorias, select sort",
      ],
    },
    {
      title: "Modal Auth (public/gifts/index.php)",
      items: [
        "Modal grid 260px (painel preto editorial) + 1fr (form)",
        "Painel preto exibe presente sendo reservado: nome em Fraunces, número, preço",
        "Tabs em segmented pill (não nav-tabs do Bootstrap)",
        "Inputs com underline only — sem border completa",
      ],
    },
    {
      title: "Pix (public/pix/index.php)",
      items: [
        "Grid 1fr / 1.05fr — coluna preta (dados/QR) + coluna branca (form)",
        "Input de valor: Fraunces 96px, prefixo R$ separado",
        "Pills de sugestão R$ 50/100/150/250/500",
        "Botão 'Copiar código' alterna estado (texto → ✓ copiado, fundo roxo 1.5s)",
      ],
    },
    {
      title: "Meus Presentes",
      items: [
        "Cards horizontais 180px · 1fr · auto",
        "Visual quadrado 180×180 do lado esquerdo",
        "Status com bullet colorido + Mono uppercase em vez de badge Bootstrap",
        "CTA secundário 'Cancelar' em ghost; primário 'Dar via Pix →' em preto",
      ],
    },
    {
      title: "Motion",
      items: [
        "Adicionar IntersectionObserver para entry animations (data-enter='up'/'fade')",
        "FLIP technique no filtro de presentes (gravar bounding rects antes de re-render)",
        "Modal: keyframes modalIn / modalOut — usar @keyframes próprio, não Bootstrap fade",
        "QR Pix: estado 'copiado' apenas troca className, sem JS pesado",
      ],
    },
  ];

  return (
    <div style={{ background: "#F6F2EA", color: "#0E0E12", padding: 56, fontFamily: "'Söhne','Inter',sans-serif" }}>
      <Tag style={{ marginBottom: 18 }}>capítulo 06 · handoff</Tag>
      <h2 style={{ fontFamily: "'Fraunces', serif", fontWeight: 300, fontSize: 72, margin: 0, letterSpacing: "-0.045em", lineHeight: 0.95 }}>
        Checklist para o <i>front-end.</i>
      </h2>
      <p style={{ fontSize: 15, color: "#3a363f", marginTop: 18, maxWidth: 720, lineHeight: 1.55 }}>
        Mantém PHP server-rendered, Bootstrap 5 grid utilities (mas substituindo
        componentes visuais por classes próprias). Sem bundler, sem framework JS.
      </p>

      <div style={{ marginTop: 40, display: "grid", gridTemplateColumns: "1fr 1fr", gap: 14 }}>
        {sections.map((s, i) => (
          <div key={s.title} style={{ background: "#fff", border: "1px solid #ECE6DA", borderRadius: 4, padding: 22 }}>
            <div style={{ display: "flex", justifyContent: "space-between", marginBottom: 14 }}>
              <span style={{ fontFamily: "'Fraunces', serif", fontWeight: 400, fontSize: 22, letterSpacing: "-0.01em" }}>{s.title}</span>
              <span style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.16em", color: "#9a8fb6" }}>0{i + 1}</span>
            </div>
            <ul style={{ margin: 0, padding: 0, listStyle: "none", display: "grid", gap: 10 }}>
              {s.items.map((it) => (
                <li key={it} style={{ display: "grid", gridTemplateColumns: "16px 1fr", gap: 10, fontSize: 13, color: "#3a363f", lineHeight: 1.5 }}>
                  <span style={{ color: "#5E2AC9", fontFamily: "'JetBrains Mono', monospace", fontSize: 11 }}>▸</span>
                  <span>{it}</span>
                </li>
              ))}
            </ul>
          </div>
        ))}
      </div>

      {/* COPY block */}
      <div style={{ marginTop: 40, background: "#0E0E12", color: "#F6F2EA", borderRadius: 4, padding: 36 }}>
        <Tag kind="default" style={{ background: "rgba(255,255,255,0.08)", color: "#c6becf", border: "none", marginBottom: 18 }}>
          copy · pt-BR — exemplos prontos
        </Tag>
        <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 36, marginTop: 24 }}>
          <div style={{ display: "grid", gap: 14 }}>
            {[
              ["Hero (Home)", "Para a Mariana, que faz 30."],
              ["Sub-hero",    "Não é uma lista de presentes — é um caderno de coisas pequenas e específicas que ela vem mencionando desde janeiro."],
              ["CTA primário","Ver os presentes →"],
              ["CTA Pix",     "Contribuir via Pix"],
              ["Lista hero",  "Vinte e quatro coisas que ela quer."],
              ["Filtros",     "Buscar por nome ou categoria…"],
            ].map(([k, v]) => (
              <div key={k} style={{ borderTop: "1px solid rgba(255,255,255,0.12)", paddingTop: 12 }}>
                <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.16em", color: "#9a8fb6", textTransform: "uppercase", marginBottom: 6 }}>{k}</div>
                <div style={{ fontSize: 14, color: "#e9defe", lineHeight: 1.5 }}>{v}</div>
              </div>
            ))}
          </div>
          <div style={{ display: "grid", gap: 14 }}>
            {[
              ["Estado esgotado", "já reservado · obrigada ✓"],
              ["Pix hero",        "Não precisa escolher. Pode contribuir um pouquinho."],
              ["Pix confiança",   "Você verá o pagamento aparecer aqui assim que for compensado. Sem cadastro, sem login."],
              ["Modal auth",      "Pra reservar, a gente só precisa do seu nome — assim eu não compro repetido."],
              ["Sucesso reserva", "Pronto. Reservado pra você por 7 dias."],
              ["Erro",            "Algo deu errado aqui — tenta de novo em alguns segundos?"],
            ].map(([k, v]) => (
              <div key={k} style={{ borderTop: "1px solid rgba(255,255,255,0.12)", paddingTop: 12 }}>
                <div style={{ fontFamily: "'JetBrains Mono', monospace", fontSize: 10, letterSpacing: "0.16em", color: "#9a8fb6", textTransform: "uppercase", marginBottom: 6 }}>{k}</div>
                <div style={{ fontSize: 14, color: "#e9defe", lineHeight: 1.5 }}>{v}</div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

window.MotionCard = MotionCard;
window.ImplementationCard = ImplementationCard;
