import React from "react";
import { Audio } from "@remotion/media";
import {
  AbsoluteFill,
  Easing,
  interpolate,
  Sequence,
  spring,
  staticFile,
  useCurrentFrame,
  useVideoConfig,
} from "remotion";
import { loadFont } from "@remotion/google-fonts/BeVietnamPro";
import {
  openaiAnnouncement,
  type AnnouncementBeat,
} from "./openaiAnnouncementData";

const { fontFamily } = loadFont("normal", {
  weights: ["400", "500", "700", "800"],
  subsets: ["vietnamese", "latin"],
});

const colors = {
  bg: "#050607",
  panel: "#101214",
  text: "#f4f7f4",
  muted: "#aeb7b2",
  dim: "#66716b",
  green: "#65d46e",
  orange: "#ff9f43",
  blue: "#66b5ff",
  violet: "#a78bfa",
  red: "#ff6f61",
};

const accentMap: Record<AnnouncementBeat["accent"], string> = {
  green: colors.green,
  orange: colors.orange,
  blue: colors.blue,
  violet: colors.violet,
  red: colors.red,
};

const clamp = {
  extrapolateLeft: "clamp" as const,
  extrapolateRight: "clamp" as const,
};

const OpenAIMark: React.FC<{ size?: number }> = ({ size = 88 }) => (
  <svg width={size} height={size} viewBox="0 0 100 100" aria-hidden="true">
    <circle cx="50" cy="50" r="45" fill="rgba(255,255,255,0.06)" />
    <path
      d="M50 16c8 0 15 4 19 10 7 1 13 6 16 13 3 8 1 16-4 22 1 8-3 16-10 20s-16 4-23 0c-7 4-16 4-23 0-7-5-11-12-10-20-5-6-7-14-4-22 3-7 9-12 16-13 4-6 11-10 19-10Zm0 11c-5 0-9 2-12 6l-2 3-4 1c-5 0-9 3-11 8-2 4-1 9 2 13l3 3-1 4c-1 5 1 9 5 12s9 3 14 0l4-2 4 2c5 3 10 3 14 0s6-7 5-12l-1-4 3-3c3-4 4-9 2-13-2-5-6-8-11-8l-4-1-2-3c-3-4-7-6-12-6Z"
      fill={colors.text}
      opacity={0.92}
    />
    <circle cx="50" cy="50" r="10" fill={colors.bg} opacity={0.88} />
  </svg>
);

const MiniBars: React.FC<{ frame: number; accent: string }> = ({
  frame,
  accent,
}) => {
  return (
    <div
      style={{
        display: "grid",
        gridTemplateColumns: "repeat(16, 1fr)",
        gap: 7,
        height: 96,
        alignItems: "end",
      }}
    >
      {Array.from({ length: 16 }).map((_, index) => {
        const wave = Math.sin(frame / 7 + index * 0.7);
        const height = interpolate(wave, [-1, 1], [22, 88]);
        return (
          <div
            key={index}
            style={{
              height,
              borderRadius: 99,
              background:
                index % 3 === 0 ? accent : "rgba(255,255,255,0.16)",
              boxShadow:
                index % 3 === 0 ? `0 0 28px ${accent}66` : "none",
            }}
          />
        );
      })}
    </div>
  );
};

const BeatCard: React.FC<{
  beat: AnnouncementBeat;
  isActive: boolean;
  index: number;
  frame: number;
}> = ({ beat, isActive, index, frame }) => {
  const accent = accentMap[beat.accent];
  const localFrame = frame - beat.startSec * 30;
  const reveal = spring({
    frame: localFrame,
    fps: 30,
    config: { damping: 18, stiffness: 90, mass: 0.7 },
  });

  return (
    <div
      style={{
        borderRadius: 26,
        border: `1px solid ${
          isActive ? `${accent}aa` : "rgba(255,255,255,0.11)"
        }`,
        background: isActive
          ? `linear-gradient(140deg, ${accent}22, rgba(255,255,255,0.045))`
          : "rgba(255,255,255,0.038)",
        padding: "22px 24px",
        opacity: isActive ? 1 : 0.45,
        transform: `scale(${isActive ? interpolate(reveal, [0, 1], [0.97, 1]) : 1})`,
        boxShadow: isActive ? `0 0 44px ${accent}22` : "none",
      }}
    >
      <div
        style={{
          display: "flex",
          alignItems: "center",
          justifyContent: "space-between",
          gap: 16,
        }}
      >
        <div
          style={{
            color: isActive ? accent : colors.dim,
            fontSize: 22,
            fontWeight: 800,
            textTransform: "uppercase",
          }}
        >
          {beat.eyebrow}
        </div>
        <div
          style={{
            width: 36,
            height: 36,
            borderRadius: 99,
            border: `1px solid ${isActive ? accent : "rgba(255,255,255,0.15)"}`,
            display: "grid",
            placeItems: "center",
            color: isActive ? accent : colors.dim,
            fontSize: 18,
            fontWeight: 800,
          }}
        >
          {index + 1}
        </div>
      </div>
      <div
        style={{
          marginTop: 14,
          color: colors.text,
          fontSize: 30,
          lineHeight: 1.18,
          fontWeight: 800,
        }}
      >
        {beat.headline}
      </div>
    </div>
  );
};

const Caption: React.FC<{ beat: AnnouncementBeat; frame: number }> = ({
  beat,
  frame,
}) => {
  const localFrame = frame - beat.startSec * 30;
  const opacity = interpolate(localFrame, [0, 8, 160, 190], [0, 1, 1, 0], {
    ...clamp,
    easing: Easing.bezier(0.16, 1, 0.3, 1),
  });

  return (
    <div
      style={{
        position: "absolute",
        left: 86,
        right: 86,
        bottom: 160,
        opacity,
        transform: `translateY(${interpolate(opacity, [0, 1], [22, 0])}px)`,
      }}
    >
      <div
        style={{
          borderRadius: 28,
          background: "rgba(0,0,0,0.62)",
          border: "1px solid rgba(255,255,255,0.13)",
          padding: "30px 36px",
          boxShadow: "0 30px 90px rgba(0,0,0,0.42)",
        }}
      >
        <div
          style={{
            color: colors.text,
            fontSize: 39,
            lineHeight: 1.28,
            fontWeight: 800,
            textAlign: "center",
          }}
        >
          {beat.subtitle}
        </div>
      </div>
    </div>
  );
};

export const OpenAIAnnouncementShort: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps, durationInFrames } = useVideoConfig();
  const time = frame / fps;
  const beat =
    openaiAnnouncement.beats.find(
      (item) => time >= item.startSec && time < item.endSec
    ) ?? openaiAnnouncement.beats[openaiAnnouncement.beats.length - 1];
  const activeIndex = openaiAnnouncement.beats.findIndex(
    (item) => item.id === beat.id
  );
  const accent = accentMap[beat.accent];

  const intro = spring({
    frame,
    fps,
    config: { damping: 20, stiffness: 70, mass: 0.8 },
  });
  const outro = interpolate(
    frame,
    [durationInFrames - 30, durationInFrames],
    [1, 0],
    {
      ...clamp,
      easing: Easing.bezier(0.65, 0, 0.35, 1),
    }
  );

  return (
    <AbsoluteFill
      style={{
        backgroundColor: colors.bg,
        color: colors.text,
        fontFamily,
        overflow: "hidden",
      }}
    >
      {openaiAnnouncement.voiceTracks.map((track) => (
        <Sequence
          key={track.src}
          from={Math.round(track.startSec * fps)}
          layout="none"
        >
          <Audio src={staticFile(track.src)} />
        </Sequence>
      ))}
      <AbsoluteFill
        style={{
          background:
            "radial-gradient(circle at 50% 20%, rgba(101,212,110,0.20), transparent 28%), radial-gradient(circle at 18% 76%, rgba(255,159,67,0.14), transparent 24%), linear-gradient(180deg, #050607, #0a0d10 58%, #050607)",
        }}
      />
      <div
        style={{
          position: "absolute",
          inset: 0,
          backgroundImage:
            "linear-gradient(rgba(255,255,255,0.028) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.028) 1px, transparent 1px)",
          backgroundSize: "72px 72px",
          opacity: 0.32,
        }}
      />

      <div
        style={{
          position: "absolute",
          left: 90,
          top: 84,
          width: 900,
          height: 1670,
          borderRadius: 62,
          border: "1px solid rgba(255,255,255,0.11)",
          background:
            "linear-gradient(180deg, rgba(255,255,255,0.075), rgba(255,255,255,0.025))",
          boxShadow: "0 80px 180px rgba(0,0,0,0.54)",
          overflow: "hidden",
          opacity: outro,
          transform: `scale(${interpolate(intro, [0, 1], [0.93, 1])})`,
        }}
      >
        <div
          style={{
            position: "absolute",
            inset: 0,
            background: `radial-gradient(circle at 54% 25%, ${accent}22, transparent 36%)`,
          }}
        />

        <div style={{ position: "relative", padding: "70px 62px 0" }}>
          <div
            style={{
              display: "flex",
              alignItems: "center",
              justifyContent: "space-between",
              gap: 20,
            }}
          >
            <div
              style={{
                display: "flex",
                alignItems: "center",
                gap: 16,
                color: colors.muted,
                fontSize: 24,
                fontWeight: 800,
              }}
            >
              <OpenAIMark size={56} />
              <span>{openaiAnnouncement.sourceLabel}</span>
            </div>
            <div
              style={{
                color: colors.dim,
                fontSize: 22,
                fontWeight: 700,
              }}
            >
              19.05.2026
            </div>
          </div>

          <div
            style={{
              marginTop: 72,
              opacity: interpolate(frame, [10, 42], [0, 1], clamp),
              transform: `translateY(${interpolate(frame, [10, 42], [36, 0], clamp)}px)`,
            }}
          >
            <div
              style={{
                fontSize: 48,
                lineHeight: 1.05,
                fontWeight: 800,
                color: colors.text,
              }}
            >
              {openaiAnnouncement.titleLead}
            </div>
            <div
              style={{
                fontSize: 86,
                lineHeight: 1.02,
                fontWeight: 800,
                color: accent,
                marginTop: 6,
                textShadow: `0 0 52px ${accent}55`,
              }}
            >
              {openaiAnnouncement.titleHighlight}
            </div>
          </div>

          <div
            style={{
              marginTop: 52,
              borderRadius: 30,
              border: "1px solid rgba(255,255,255,0.11)",
              background: "rgba(0,0,0,0.22)",
              padding: "28px 30px 34px",
            }}
          >
            <div
              style={{
                display: "flex",
                alignItems: "center",
                justifyContent: "space-between",
                gap: 22,
                color: colors.muted,
                fontSize: 24,
                fontWeight: 700,
                marginBottom: 26,
              }}
            >
              <span>voiceover + subtitle</span>
              <span style={{ color: accent }}>live summary</span>
            </div>
            <MiniBars frame={frame} accent={accent} />
          </div>

          <div
            style={{
              display: "grid",
              gridTemplateColumns: "1fr",
              gap: 14,
              marginTop: 34,
            }}
          >
            {openaiAnnouncement.beats.map((item, index) => (
              <BeatCard
                key={item.id}
                beat={item}
                index={index}
                isActive={index === activeIndex}
                frame={frame}
              />
            ))}
          </div>

          <div
            style={{
              marginTop: 34,
              color: colors.dim,
              fontSize: 22,
              fontWeight: 700,
              textAlign: "center",
            }}
          >
            {openaiAnnouncement.footer}
          </div>
        </div>
      </div>

      <Caption beat={beat} frame={frame} />
    </AbsoluteFill>
  );
};
