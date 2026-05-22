import "./index.css";
import { Composition } from "remotion";
import { OpenAIAnnouncementShort } from "./OpenAIAnnouncementShort";
import { StoryComposition } from "./Composition";
import { ExplainerShort } from "./ExplainerShort";
import {
  OPENAI_ANNOUNCEMENT_DURATION_SEC,
  OPENAI_ANNOUNCEMENT_FPS,
  OPENAI_ANNOUNCEMENT_HEIGHT,
  OPENAI_ANNOUNCEMENT_WIDTH,
} from "./openaiAnnouncementData";
import {
  EXPLAINER_DURATION_SEC,
  EXPLAINER_FPS,
  EXPLAINER_HEIGHT,
  EXPLAINER_WIDTH,
} from "./explainerData";
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
      <Composition
        id="ExplainerShort"
        component={ExplainerShort}
        durationInFrames={Math.round(EXPLAINER_DURATION_SEC * EXPLAINER_FPS)}
        fps={EXPLAINER_FPS}
        width={EXPLAINER_WIDTH}
        height={EXPLAINER_HEIGHT}
      />
      <Composition
        id="OpenAIAnnouncementShort"
        component={OpenAIAnnouncementShort}
        durationInFrames={Math.round(
          OPENAI_ANNOUNCEMENT_DURATION_SEC * OPENAI_ANNOUNCEMENT_FPS
        )}
        fps={OPENAI_ANNOUNCEMENT_FPS}
        width={OPENAI_ANNOUNCEMENT_WIDTH}
        height={OPENAI_ANNOUNCEMENT_HEIGHT}
      />
    </>
  );
};
