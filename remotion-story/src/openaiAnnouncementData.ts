export type AnnouncementBeat = {
  id: string;
  startSec: number;
  endSec: number;
  eyebrow: string;
  headline: string;
  subtitle: string;
  accent: "green" | "orange" | "blue" | "violet" | "red";
};

export type AnnouncementVoiceTrack = {
  src: string;
  startSec: number;
};

export const OPENAI_ANNOUNCEMENT_FPS = 30;
export const OPENAI_ANNOUNCEMENT_WIDTH = 1080;
export const OPENAI_ANNOUNCEMENT_HEIGHT = 1920;
export const OPENAI_ANNOUNCEMENT_DURATION_SEC = 61;

export const openaiAnnouncement = {
  sourceLabel: "OpenAI Docs • GPT-5.5",
  handle: "@yourbrand",
  titleLead: "OpenAI vừa cập nhật",
  titleHighlight: "GPT-5.5",
  footer: "Tóm tắt từ tài liệu chính thức của OpenAI",
  voiceTracks: [
    { src: "openai-announcement-clear.mp3", startSec: 0.4 },
  ] satisfies AnnouncementVoiceTrack[],
  beats: [
    {
      id: "hook",
      startSec: 0,
      endSec: 9,
      eyebrow: "Tin mới",
      headline: "GPT-5.5 không chỉ là đổi tên model",
      subtitle:
        "OpenAI cập nhật tài liệu GPT-5.5. Tóm tắt nhanh điểm đáng chú ý.",
      accent: "green",
    },
    {
      id: "production",
      startSec: 9,
      endSec: 21,
      eyebrow: "Dành cho ai?",
      headline: "Dành cho việc khó",
      subtitle:
        "Viết code, dùng công cụ, xử lý ngữ cảnh dài và hỗ trợ khách hàng.",
      accent: "orange",
    },
    {
      id: "reasoning",
      startSec: 21,
      endSec: 31,
      eyebrow: "Điểm mới 1",
      headline: "Reasoning hiệu quả hơn",
      subtitle:
        "Việc nhiều bước có thể tốn ít token suy luận hơn.",
      accent: "blue",
    },
    {
      id: "tool-use",
      startSec: 31,
      endSec: 41,
      eyebrow: "Điểm mới 2",
      headline: "Gọi công cụ chính xác hơn",
      subtitle:
        "Agent đáng tin hơn khi phải chạy qua nhiều hệ thống.",
      accent: "violet",
    },
    {
      id: "behavior",
      startSec: 41,
      endSec: 50,
      eyebrow: "Thay đổi hành vi",
      headline: "Mặc định là medium",
      subtitle:
        "Medium cân bằng chất lượng, tốc độ và chi phí.",
      accent: "red",
    },
    {
      id: "migration",
      startSec: 50,
      endSec: 61,
      eyebrow: "Cách migration",
      headline: "Viết prompt theo kết quả",
      subtitle:
        "Nói rõ kết quả cần đạt, rồi kiểm thử bằng ví dụ thật.",
      accent: "green",
    },
  ] satisfies AnnouncementBeat[],
};
