import "./index.css";
import { Composition } from "remotion";
import { StoryComposition } from "./Composition";
import { FPS, HEIGHT, TOTAL_DURATION_SEC, WIDTH } from "./scenes";

export const RemotionRoot: React.FC = () => {
  return (
    <>
      <Composition
        id="Story"
        component={StoryComposition}
        durationInFrames={Math.round(TOTAL_DURATION_SEC * FPS)}
        fps={FPS}
        width={WIDTH}
        height={HEIGHT}
      />
    </>
  );
};
