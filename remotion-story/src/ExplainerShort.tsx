import React from "react";
import {
  AbsoluteFill,
  Easing,
  interpolate,
  spring,
  useCurrentFrame,
  useVideoConfig,
} from "remotion";
import { loadFont } from "@remotion/google-fonts/BeVietnamPro";
import { explainerData, type ExplainerCard } from "./explainerData";

const { fontFamily } = loadFont("normal", {
  weights: ["400", "500", "700", "800"],
  subsets: ["vietnamese", "latin"],
});

const palette = {
  bg: "#050505",
  panel: "#0b0a09",
  panelSoft: "#13110f",
  border: "rgba(255, 255, 255, 0.14)",
  borderStrong: "rgba(255, 255, 255, 0.38)",
  text: "#f5f2ed",
  muted: "#b9b2aa",
  dim: "#7f7871",
  orange: "#ff9f43",
  orangeDeep: "#c86522",
  green: "#8ed889",
};

const Icon: React.FC<{ type: ExplainerCard["icon"]; size?: number }> = ({
  type,
  size = 64,
}) => {
  const stroke = "currentColor";
  const common = {
    fill: "none",
    stroke,
    strokeWidth: 2.4,
    strokeLinecap: "round" as const,
    strokeLinejoin: "round" as const,
  };

  if (type === "loop") {
    return (
      <svg width={size} height={size} viewBox="0 0 64 64" aria-hidden="true">
        <path {...common} d="M18 32a14 14 0 0 1 24-10" />
        <path {...common} d="M42 15v8h-8" />
        <path {...common} d="M46 32a14 14 0 0 1-24 10" />
        <path {...common} d="M22 49v-8h8" />
      </svg>
    );
  }

  if (type === "habit") {
    return (
      <svg width={size} height={size} viewBox="0 0 64 64" aria-hidden="true">
        <path {...common} d="M20 18h24" />
        <path {...common} d="M18 30h28" />
        <path {...common} d="M22 42h20" />
        <path {...common} d="M16 22a6 6 0 0 1 6-6h20a6 6 0 0 1 6 6v20a6 6 0 0 1-6 6H22a6 6 0 0 1-6-6Z" />
      </svg>
    );
  }

  if (type === "cloud") {
    return (
      <svg width={size} height={size} viewBox="0 0 64 64" aria-hidden="true">
        <path {...common} d="M22 44h24a9 9 0 0 0 1-18 15 15 0 0 0-29-4 11 11 0 0 0 4 22Z" />
        <path {...common} d="M32 30v18" />
        <path {...common} d="m25 37 7-7 7 7" />
      </svg>
    );
  }

  return (
    <svg width={size} height={size} viewBox="0 0 64 64" aria-hidden="true">
      <path {...common} d="M20 40 8 32l12-8" />
      <path {...common} d="m44 24 12 8-12 8" />
      <path {...common} d="m36 16-8 32" />
      <circle {...common} cx="32" cy="32" r="26" />
    </svg>
  );
};

const FlameMark: React.FC<{ frame: number }> = ({ frame }) => {
  const pulse = interpolate(Math.sin(frame / 8), [-1, 1], [0.92, 1.06]);

  return (
    <div
      style={{
        width: 128,
        height: 128,
        borderRadius: 34,
        background:
          "linear-gradient(145deg, rgba(255,255,255,0.12), rgba(255,255,255,0.02))",
        border: `1px solid ${palette.border}`,
        display: "grid",
        placeItems: "center",
        boxShadow:
          "0 20px 70px rgba(255, 150, 55, 0.16), inset 0 1px 0 rgba(255,255,255,0.12)",
        transform: `scale(${pulse})`,
      }}
    >
      <svg width="70" height="70" viewBox="0 0 80 80" aria-hidden="true">
        <path
          d="M44 8c2 13 16 17 16 36 0 15-10 26-21 26-13 0-23-10-23-23 0-12 8-22 18-29-1 10 4 15 10 17 5-9 2-18 0-27Z"
          fill={palette.text}
        />
        <path
          d="M43 42c3 6 10 8 10 17 0 7-5 12-13 12s-13-5-13-12c0-7 5-13 12-18-1 6 1 9 4 11 2-3 2-7 0-10Z"
          fill={palette.panel}
        />
      </svg>
    </div>
  );
};

const BackgroundCard: React.FC<{
  card: ExplainerCard;
  index: number;
  frame: number;
}> = ({ card, index, frame }) => {
  const positions = [
    { left: -205, top: 280 },
    { left: 610, top: 300 },
    { left: -185, top: 1130 },
    { left: 620, top: 1130 },
  ];
  const drift = interpolate(Math.sin((frame + index * 18) / 42), [-1, 1], [-16, 16]);

  return (
    <div
      style={{
        position: "absolute",
        left: positions[index].left,
        top: positions[index].top + drift,
        width: 610,
        height: 370,
        borderRadius: 38,
        border: "1px solid rgba(255,255,255,0.08)",
        background:
          "linear-gradient(150deg, rgba(255,255,255,0.10), rgba(255,255,255,0.018))",
        filter: "blur(3px)",
        opacity: 0.28,
        padding: 56,
        transform: "scale(1.18)",
        color: "rgba(255,255,255,0.66)",
      }}
    >
      <div style={{ opacity: 0.72 }}>
        <Icon type={card.icon} size={92} />
      </div>
      <div
        style={{
          fontSize: 50,
          lineHeight: 1.18,
          fontWeight: 800,
          marginTop: 40,
        }}
      >
        {card.title}
      </div>
    </div>
  );
};

const ContentCard: React.FC<{
  card: ExplainerCard;
  index: number;
  frame: number;
}> = ({ card, index, frame }) => {
  const start = 82 + index * 18;
  const progress = spring({
    frame: frame - start,
    fps: 30,
    config: {
      damping: 17,
      stiffness: 90,
      mass: 0.7,
    },
  });
  const glow = interpolate(frame, [start, start + 16, start + 46], [0, 1, 0], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
    easing: Easing.bezier(0.16, 1, 0.3, 1),
  });

  return (
    <div
      style={{
        position: "relative",
        height: 306,
        borderRadius: 22,
        border: `1px solid ${
          index === 0 ? palette.borderStrong : palette.border
        }`,
        background:
          "linear-gradient(145deg, rgba(255,255,255,0.10), rgba(255,255,255,0.025))",
        boxShadow: `0 0 ${34 * glow}px rgba(255, 159, 67, ${0.34 * glow}), inset 0 1px 0 rgba(255,255,255,0.10)`,
        padding: "28px 24px 24px",
        color: palette.text,
        opacity: progress,
        transform: `translateY(${interpolate(progress, [0, 1], [42, 0])}px) scale(${interpolate(progress, [0, 1], [0.92, 1])})`,
        overflow: "hidden",
      }}
    >
      <div
        style={{
          position: "absolute",
          inset: 0,
          background:
            index === 0
              ? "linear-gradient(150deg, rgba(255,159,67,0.14), rgba(255,255,255,0))"
              : "transparent",
        }}
      />
      <div style={{ position: "relative", color: palette.text }}>
        <Icon type={card.icon} size={56} />
      </div>
      <div
        style={{
          position: "relative",
          fontSize: 31,
          lineHeight: 1.18,
          fontWeight: 800,
          marginTop: 18,
          letterSpacing: 0,
        }}
      >
        {card.title}
      </div>
      <div
        style={{
          position: "relative",
          fontSize: 23,
          lineHeight: 1.28,
          fontWeight: 500,
          color: palette.muted,
          marginTop: 12,
        }}
      >
        {card.body}
      </div>
    </div>
  );
};

export const ExplainerShort: React.FC = () => {
  const frame = useCurrentFrame();
  const { durationInFrames } = useVideoConfig();

  const intro = spring({
    frame,
    fps: 30,
    config: {
      damping: 18,
      stiffness: 74,
      mass: 0.8,
    },
  });
  const exit = interpolate(
    frame,
    [durationInFrames - 24, durationInFrames],
    [1, 0.94],
    {
      extrapolateLeft: "clamp",
      extrapolateRight: "clamp",
      easing: Easing.bezier(0.65, 0, 0.35, 1),
    }
  );
  const panelScale = interpolate(intro, [0, 1], [0.88, 1]) * exit;
  const panelOpacity = interpolate(intro, [0, 1], [0, 1]);
  const titleY = interpolate(frame, [30, 58], [38, 0], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
    easing: Easing.bezier(0.16, 1, 0.3, 1),
  });
  const closingOpacity = interpolate(
    frame,
    [durationInFrames - 84, durationInFrames - 52],
    [0, 1],
    {
      extrapolateLeft: "clamp",
      extrapolateRight: "clamp",
      easing: Easing.bezier(0.16, 1, 0.3, 1),
    }
  );

  return (
    <AbsoluteFill
      style={{
        backgroundColor: palette.bg,
        fontFamily,
        color: palette.text,
        overflow: "hidden",
      }}
    >
      <AbsoluteFill
        style={{
          background:
            "linear-gradient(180deg, #050505 0%, #0b0805 54%, #050505 100%)",
        }}
      />
      <div
        style={{
          position: "absolute",
          left: 0,
          right: 0,
          bottom: 0,
          height: 760,
          background:
            "linear-gradient(0deg, rgba(190,86,28,0.22), rgba(190,86,28,0))",
        }}
      />
      {explainerData.cards.map((card, index) => (
        <BackgroundCard
          key={card.id}
          card={card}
          index={index}
          frame={frame}
        />
      ))}

      <div
        style={{
          position: "absolute",
          left: 150,
          top: 95,
          width: 780,
          height: 1730,
          borderRadius: 58,
          border: "1px solid rgba(255,255,255,0.10)",
          background:
            "linear-gradient(180deg, rgba(255,255,255,0.045), rgba(255,255,255,0.012))",
          boxShadow: "0 70px 190px rgba(0,0,0,0.72)",
          opacity: panelOpacity,
          transform: `scale(${panelScale})`,
          transformOrigin: "center",
          overflow: "hidden",
        }}
      >
        <div
          style={{
            position: "absolute",
            inset: 0,
            background:
              "linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0) 38%, rgba(255,122,28,0.08) 100%)",
          }}
        />

        <div
          style={{
            position: "relative",
            padding: "96px 62px 0",
          }}
        >
          <div
            style={{
              display: "flex",
              alignItems: "center",
              gap: 14,
              color: palette.dim,
              fontSize: 24,
              fontWeight: 700,
            }}
          >
            <div
              style={{
                width: 28,
                height: 28,
                borderRadius: 99,
                background: palette.green,
                color: "#163016",
                display: "grid",
                placeItems: "center",
                fontSize: 19,
                fontWeight: 800,
              }}
            >
              Y
            </div>
            <span>{explainerData.brand}</span>
          </div>

          <div
            style={{
              display: "grid",
              placeItems: "center",
              marginTop: 62,
              opacity: interpolate(frame, [14, 42], [0, 1], {
                extrapolateLeft: "clamp",
                extrapolateRight: "clamp",
              }),
              transform: `translateY(${interpolate(frame, [14, 42], [24, 0], {
                extrapolateLeft: "clamp",
                extrapolateRight: "clamp",
              })}px)`,
            }}
          >
            <FlameMark frame={frame} />
          </div>

          <div
            style={{
              marginTop: 42,
              textAlign: "center",
              fontSize: 50,
              lineHeight: 1.08,
              fontWeight: 800,
              letterSpacing: 0,
              opacity: interpolate(frame, [34, 64], [0, 1], {
                extrapolateLeft: "clamp",
                extrapolateRight: "clamp",
              }),
              transform: `translateY(${titleY}px)`,
            }}
          >
            {explainerData.titleLead}{" "}
            <span style={{ color: palette.orange }}>
              {explainerData.titleHighlight}
            </span>
          </div>

          <div
            style={{
              margin: "18px auto 0",
              width: "max-content",
              maxWidth: "100%",
              padding: "8px 18px",
              borderRadius: 99,
              border: "1px solid rgba(255,255,255,0.10)",
              color: palette.muted,
              fontSize: 22,
              fontWeight: 700,
              background: "rgba(255,255,255,0.035)",
              opacity: interpolate(frame, [46, 72], [0, 1], {
                extrapolateLeft: "clamp",
                extrapolateRight: "clamp",
              }),
            }}
          >
            {explainerData.badge}
          </div>

          <div
            style={{
              display: "grid",
              gridTemplateColumns: "1fr 1fr",
              gap: 18,
              marginTop: 58,
            }}
          >
            {explainerData.cards.map((card, index) => (
              <ContentCard
                key={card.id}
                card={card}
                index={index}
                frame={frame}
              />
            ))}
          </div>

          <div
            style={{
              margin: "72px auto 0",
              width: 555,
              textAlign: "center",
              fontSize: 30,
              lineHeight: 1.32,
              color: palette.text,
              fontWeight: 700,
              opacity: closingOpacity,
            }}
          >
            {explainerData.closingLine}
          </div>
        </div>

        <div
          style={{
            position: "absolute",
            left: 0,
            right: 0,
            bottom: 42,
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            gap: 10,
            color: palette.dim,
            fontSize: 24,
            fontWeight: 700,
            opacity: 0.9,
          }}
        >
          <div
            style={{
              width: 24,
              height: 24,
              borderRadius: 99,
              background: palette.green,
              color: "#163016",
              display: "grid",
              placeItems: "center",
              fontSize: 16,
              fontWeight: 800,
            }}
          >
            Y
          </div>
          <span>{explainerData.brand}</span>
        </div>
      </div>
    </AbsoluteFill>
  );
};
