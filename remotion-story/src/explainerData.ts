export type ExplainerCard = {
  id: string;
  icon: "loop" | "habit" | "cloud" | "source";
  title: string;
  body: string;
};

export const EXPLAINER_FPS = 30;
export const EXPLAINER_WIDTH = 1080;
export const EXPLAINER_HEIGHT = 1920;
export const EXPLAINER_DURATION_SEC = 18;

export const explainerData = {
  brand: "@yourbrand",
  badge: "AI workflow",
  titleLead: "Lộ Trình",
  titleHighlight: "Máu Chiến",
  closingLine: "Ít bước hơn, nhưng mỗi bước phải chạy được thật.",
  cards: [
    {
      id: "learning-loop",
      icon: "loop",
      title: "Vòng lặp tự học",
      body: "Ghi kết quả, rút kinh nghiệm, rồi cải thiện prompt và quy trình.",
    },
    {
      id: "working-habit",
      icon: "habit",
      title: "Giữ thói quen cũ",
      body: "Bắt đầu từ workflow hiện tại, sau đó tự động hóa từng đoạn.",
    },
    {
      id: "cloud-agent",
      icon: "cloud",
      title: "Đưa Agent lên Cloud",
      body: "Cho tác vụ chạy nền trên VPS hoặc cloud khi cần scale.",
    },
    {
      id: "open-source",
      icon: "source",
      title: "Mở để tùy biến sâu",
      body: "Kiểm soát model, dữ liệu, tool và chi phí vận hành.",
    },
  ] satisfies ExplainerCard[],
};
