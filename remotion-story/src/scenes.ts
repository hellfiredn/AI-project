export type Scene = {
  id: string;
  startSec: number;
  durationSec: number;
  mediaSrc: string;
  mediaType: "video" | "image";
  caption: string;
};

export const FPS = 30;
export const WIDTH = 1080;
export const HEIGHT = 1920;

export const VOICEOVER_SRC = "narration.mp3";
export const MUSIC_SRC = "music.mp3";
export const MUSIC_VOLUME = 0.18;

export const TOTAL_DURATION_SEC = 60;

export const scenes: Scene[] = [
  {
    id: "scene1",
    startSec: 0,
    durationSec: 10,
    mediaSrc: "scene1.mp4",
    mediaType: "video",
    caption: "Bà có một chén trà sứ\nđã sứt mẻ một góc",
  },
  {
    id: "scene2",
    startSec: 10,
    durationSec: 10,
    mediaSrc: "scene2.mp4",
    mediaType: "video",
    caption: "Tôi hỏi sao bà không dùng\nbộ mới cho đẹp",
  },
  {
    id: "scene3",
    startSec: 20,
    durationSec: 10,
    mediaSrc: "scene3.mp4",
    mediaType: "video",
    caption: "Bà kể cái chén ấy là của ông\nngày ông đi bộ đội",
  },
  {
    id: "scene4",
    startSec: 30,
    durationSec: 10,
    mediaSrc: "scene4.mp4",
    mediaType: "video",
    caption: '"Giữ giùm anh nhé"\nÔng đã nói như thế',
  },
  {
    id: "scene5",
    startSec: 40,
    durationSec: 10,
    mediaSrc: "scene5.mp4",
    mediaType: "video",
    caption: "Mỗi sáng bà rót trà\nnhư rót lại cả một đời người",
  },
  {
    id: "scene6",
    startSec: 50,
    durationSec: 10,
    mediaSrc: "scene6.mp4",
    mediaType: "video",
    caption: "Có những thứ sứt mẻ\nlại quý hơn vạn lần đồ nguyên vẹn",
  },
];
