import { AbsoluteFill, Sequence, staticFile } from "remotion";
import { Audio } from "@remotion/media";
import { loadFont } from "@remotion/google-fonts/BeVietnamPro";
import { Scene } from "./Scene";
import {
  FPS,
  MUSIC_SRC,
  MUSIC_VOLUME,
  scenes,
  VOICEOVER_SRC,
} from "./scenes";

const { fontFamily } = loadFont("normal", {
  weights: ["400", "700"],
  subsets: ["vietnamese", "latin"],
});

export const StoryComposition = () => {
  return (
    <AbsoluteFill style={{ backgroundColor: "#000", fontFamily }}>
      {scenes.map((scene) => (
        <Sequence
          key={scene.id}
          from={Math.round(scene.startSec * FPS)}
          durationInFrames={Math.round(scene.durationSec * FPS)}
        >
          <Scene scene={scene} />
        </Sequence>
      ))}

      <Sequence>
        <Audio src={staticFile(VOICEOVER_SRC)} />
      </Sequence>

      <Sequence>
        <Audio src={staticFile(MUSIC_SRC)} volume={MUSIC_VOLUME} />
      </Sequence>
    </AbsoluteFill>
  );
};
