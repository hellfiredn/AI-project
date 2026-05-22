import { createRequire } from "node:module";
import { mkdir, readFile, writeFile } from "node:fs/promises";
import path from "node:path";

const require = createRequire(import.meta.url);
const textToSpeech = require("@google-cloud/text-to-speech");

const args = parseArgs(process.argv.slice(2));

if (args.help || (!args.text && !args["text-file"])) {
  printHelp();
  process.exit(args.help ? 0 : 1);
}

const languageCode = args.language ?? "vi-VN";
const voiceName = args.voice ?? "vi-VN-Standard-A";
const outputPath = args.out ?? "public/narration.mp3";
const encoding = args.encoding ?? "MP3";
const speakingRate = Number(args["speaking-rate"] ?? 1);
const pitch = Number(args.pitch ?? 0);
const text = args.text ?? (await readFile(args["text-file"], "utf8"));

const client = new textToSpeech.TextToSpeechClient();

const request = {
  input: args.ssml ? { ssml: text } : { text },
  voice: {
    languageCode,
    name: voiceName,
  },
  audioConfig: {
    audioEncoding: encoding,
    speakingRate,
    pitch,
  },
};

const [response] = await client.synthesizeSpeech(request);

if (!response.audioContent) {
  throw new Error("Google Cloud Text-to-Speech did not return audio content.");
}

await mkdir(path.dirname(outputPath), { recursive: true });
await writeFile(outputPath, response.audioContent, "binary");

console.log(`Created ${outputPath}`);
console.log(`Voice: ${voiceName}`);
console.log(`Characters: ${text.length}`);

function parseArgs(argv) {
  const parsed = {};

  for (let index = 0; index < argv.length; index += 1) {
    const arg = argv[index];

    if (!arg.startsWith("--")) {
      continue;
    }

    const key = arg.slice(2);
    const next = argv[index + 1];

    if (!next || next.startsWith("--")) {
      parsed[key] = true;
      continue;
    }

    parsed[key] = next;
    index += 1;
  }

  return parsed;
}

function printHelp() {
  console.log(`
Usage:
  node scripts/google-tts.mjs --text-file scripts/narration.txt --out public/narration.mp3

Options:
  --text "..."                Text to synthesize
  --text-file <path>          UTF-8 text file to synthesize
  --out <path>                Output audio path, default public/narration.mp3
  --language <code>           Language code, default vi-VN
  --voice <name>              Voice name, default vi-VN-Standard-A
  --encoding <format>         MP3, LINEAR16, OGG_OPUS, default MP3
  --speaking-rate <number>    Default 1
  --pitch <number>            Default 0
  --ssml                      Treat input as SSML instead of plain text
`);
}
