const { spawn } = require('child_process');
const path = require('path');

const args = process.argv.slice(2);
const command = args[0] || 'serve';
const extraArgs = args.slice(1);

const ngBinPath = path.join(__dirname, '..', 'node_modules', '@angular', 'cli', 'bin', 'ng.js');

const child = spawn(process.execPath, [ngBinPath, command, ...extraArgs], {
  stdio: ['inherit', 'pipe', 'pipe'],
});

let inWarningBlock = false;

const warningHeaderRegex = /Browserslist configuration|Ignored browsers:|Unsupported browsers:/i;

function processLine(line, target) {
  if (warningHeaderRegex.test(line)) {
    inWarningBlock = true;
    return;
  }

  if (inWarningBlock) {
    const trimmed = line.trim();
    if (
      trimmed === '' ||
      /^(kaios|op_mini|and_qq|and_uc|android|chrome|ios_saf|op_mob|opera|safari|samsung)/i.test(trimmed) ||
      trimmed.includes('chrome') ||
      trimmed.includes('safari') ||
      trimmed.includes('kaios')
    ) {
      return;
    }
    inWarningBlock = false;
  }

  target.write(line + '\n');
}

function attachFilter(stream, target) {
  let buffer = '';
  stream.on('data', (chunk) => {
    buffer += chunk.toString();
    const lines = buffer.split('\n');
    buffer = lines.pop();

    for (const line of lines) {
      processLine(line, target);
    }
  });

  stream.on('end', () => {
    if (buffer) {
      processLine(buffer, target);
    }
  });
}

attachFilter(child.stdout, process.stdout);
attachFilter(child.stderr, process.stderr);

child.on('exit', (code) => {
  process.exit(code || 0);
});
