import {
  AbsoluteFill,
  Easing,
  Img,
  interpolate,
  staticFile,
  useCurrentFrame,
  useVideoConfig,
} from "remotion";
import { Video } from "@remotion/media";
import type { Scene as SceneType } from "./scenes";

type Props = {
  scene: SceneType;
};

export const Scene: React.FC<Props> = ({ scene }) => {
  const frame = useCurrentFrame();
  const { fps, durationInFrames } = useVideoConfig();

  const fadeIn = interpolate(frame, [0, 0.6 * fps], [0, 1], {
    extrapolateRight: "clamp",
    extrapolateLeft: "clamp",
    easing: Easing.bezier(0.16, 1, 0.3, 1),
  });

  const fadeOut = interpolate(
    frame,
    [durationInFrames - 0.6 * fps, durationInFrames],
    [1, 0],
    {
      extrapolateRight: "clamp",
      extrapolateLeft: "clamp",
      easing: Easing.bezier(0.16, 1, 0.3, 1),
    }
  );

  const opacity = Math.min(fadeIn, fadeOut);

  const zoom = interpolate(frame, [0, durationInFrames], [1.0, 1.12], {
    extrapolateRight: "clamp",
    extrapolateLeft: "clamp",
  });

  const captionDelay = 0.4 * fps;
  const captionOpacity = interpolate(
    frame,
    [captionDelay, captionDelay + 0.8 * fps],
    [0, 1],
    {
      extrapolateRight: "clamp",
      extrapolateLeft: "clamp",
      easing: Easing.bezier(0.16, 1, 0.3, 1),
    }
  );
  const captionTranslateY = interpolate(
    frame,
    [captionDelay, captionDelay + 0.8 * fps],
    [30, 0],
    {
      extrapolateRight: "clamp",
      extrapolateLeft: "clamp",
      easing: Easing.bezier(0.16, 1, 0.3, 1),
    }
  );

  return (
    <AbsoluteFill style={{ opacity, backgroundColor: "#000" }}>
      <AbsoluteFill
        style={{
          transform: `scale(${zoom})`,
          transformOrigin: "center center",
        }}
      >
        {scene.mediaType === "video" ? (
          <Video
            src={staticFile(scene.mediaSrc)}
            style={{
              width: "100%",
              height: "100%",
              objectFit: "cover",
            }}
          />
        ) : (
          <Img
            src={staticFile(scene.mediaSrc)}
            style={{
              width: "100%",
              height: "100%",
              objectFit: "cover",
            }}
          />
        )}
      </AbsoluteFill>

      <AbsoluteFill
        style={{
          background:
            "linear-gradient(to bottom, rgba(0,0,0,0) 50%, rgba(0,0,0,0.75) 100%)",
        }}
      />

      <AbsoluteFill
        style={{
          alignItems: "center",
          justifyContent: "flex-end",
          paddingBottom: 360,
          paddingLeft: 80,
          paddingRight: 80,
        }}
      >
        <div
          style={{
            opacity: captionOpacity,
            transform: `translateY(${captionTranslateY}px)`,
            color: "white",
            fontSize: 64,
            lineHeight: 1.35,
            fontWeight: 700,
            textAlign: "center",
            textShadow: "0 4px 24px rgba(0,0,0,0.6)",
            whiteSpace: "pre-line",
            letterSpacing: -0.5,
          }}
        >
          {scene.caption}
        </div>
      </AbsoluteFill>
    </AbsoluteFill>
  );
};
