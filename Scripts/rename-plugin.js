const fs = require('fs');
const path = require('path');
const readline = require('readline');

const root = path.resolve(__dirname, '..');
const scriptPath = path.resolve(__filename);
const excludedDirectories = new Set(['.git', 'node_modules', 'vendor', 'dist', 'build']);
const binaryExtensions = new Set(['.7z', '.gif', '.ico', '.jpeg', '.jpg', '.mo', '.png', '.pdf', '.tar', '.woff', '.woff2', '.zip']);

function ask(rl, question, defaultValue = '') {
  const suffix = defaultValue ? ` [${defaultValue}]` : '';
  return new Promise((resolve) => {
    rl.question(`${question}${suffix}: `, (answer) => resolve(answer.trim() || defaultValue));
  });
}

function required(value, label, pattern, example) {
  if (!value || !pattern.test(value)) {
    throw new Error(`${label} must match ${example}.`);
  }
  return value;
}

function toSlug(value) {
  return value
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .replace(/-{2,}/g, '-');
}

function toIdentifier(value) {
  return value
    .replace(/[^a-zA-Z0-9]+(.)/g, (_, character) => character.toUpperCase())
    .replace(/[^a-zA-Z0-9]/g, '');
}

function toPascalCase(value) {
  return value
    .split(/[^a-zA-Z0-9]+/)
    .filter(Boolean)
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join('');
}

function filesIn(directory) {
  return fs.readdirSync(directory, { withFileTypes: true }).flatMap((entry) => {
    const fullPath = path.join(directory, entry.name);
    if (entry.isDirectory()) {
      return excludedDirectories.has(entry.name) ? [] : filesIn(fullPath);
    }
    return fullPath === scriptPath ? [] : [fullPath];
  });
}

function isTextFile(filePath) {
  if (binaryExtensions.has(path.extname(filePath).toLowerCase())) return false;
  const buffer = fs.readFileSync(filePath);
  return !buffer.includes(0);
}

function replaceAll(value, replacements) {
  return [...replacements]
    .sort(([left], [right]) => right.length - left.length)
    .reduce((result, [from, to]) => result.split(from).join(to), value);
}

function relative(filePath) {
  return path.relative(root, filePath).replaceAll('\\', '/');
}

async function collectAnswers() {
  const rl = readline.createInterface({ input: process.stdin, output: process.stdout });
  try {
    const displayName = await ask(rl, 'Display name', 'Plugin Name');
    const slug = await ask(rl, 'Plugin slug (lowercase e.g. kebab-case)', toSlug(displayName));
    const namespace = await ask(rl, 'PHP namespace (without trailing backslash)', toPascalCase(displayName).toUpperCase());
    const className = await ask(rl, 'Main plugin class', toPascalCase(displayName));
    const shortcodePrefix = await ask(rl, 'Shortcode/settings prefix (lowercase snake_case)', slug.replaceAll('-', '_'));
    const textDomain = await ask(rl, 'Text domain (lowercase kebab-case)', slug);
    const bootstrapFilename = await ask(rl, 'Bootstrap filename', `${slug}.php`);
    const assetBasename = await ask(rl, 'Asset basename (without extension)', slug);
    const author = await ask(rl, 'Author', 'YourName');
    const authorUri = await ask(rl, 'Author URI', 'https://example.com');
    const pluginUri = await ask(rl, 'Plugin URI', `https://example.com/${slug}`);
    const packageVendor = await ask(rl, 'Composer package vendor e.g author/pluginname', slug.replaceAll('-', ''));

    required(displayName, 'Display name', /\S/, 'at least one non-space character');
    required(slug, 'Plugin slug', /^[a-z0-9]+(?:-[a-z0-9]+)*$/, 'lowercase kebab-case');
    required(namespace, 'PHP namespace', /^[A-Z][A-Za-z0-9]*(?:\\[A-Z][A-Za-z0-9]*)*$/, 'PascalCase segments separated by backslashes');
    required(className, 'Main plugin class', /^[A-Z][A-Za-z0-9]*$/, 'a PHP class name beginning with an uppercase letter');
    required(shortcodePrefix, 'Shortcode/settings prefix', /^[a-z][a-z0-9]*(?:_[a-z0-9]+)*$/, 'lowercase snake_case');
    required(textDomain, 'Text domain', /^[a-z0-9]+(?:-[a-z0-9]+)*$/, 'lowercase kebab-case');
    required(bootstrapFilename, 'Bootstrap filename', /^[a-z0-9]+(?:-[a-z0-9]+)*\.php$/, 'a lowercase PHP filename');
    required(assetBasename, 'Asset basename', /^[a-z0-9]+(?:-[a-z0-9]+)*$/, 'lowercase kebab-case');
    required(packageVendor, 'Composer package vendor', /^[a-z0-9]+(?:[-_][a-z0-9]+)*$/, 'lowercase letters, numbers, hyphens, or underscores');

    return {
      displayName,
      slug,
      namespace,
      className,
      shortcodePrefix,
      shortcode: `${shortcodePrefix}_status`,
      textDomain,
      bootstrapFilename,
      assetBasename,
      author,
      authorUri,
      pluginUri,
      packageName: `${packageVendor}/${slug}`,
      compactSlug: slug.replaceAll('-', ''),
    };
  } finally {
    rl.close();
  }
}

function buildReplacements(values) {
  return new Map([
    ['author/pluginname', values.packageName],
    ['pluginname/pluginname', values.packageName],
    ['https://example.com/plugin-name', values.pluginUri],
    ['Text Domain: pluginname', `Text Domain: ${values.textDomain}`],
    ['X-Domain: plugin-name', `X-Domain: ${values.textDomain}`],
    ["'pluginname'", `'${values.textDomain}'`],
    ['"pluginname"', `"${values.textDomain}"`],
    ['https://example.com', values.authorUri],
    ['Plugin Name', values.displayName],
    ['pluginname_status', values.shortcode],
    ['pluginname', values.shortcodePrefix],
    ['pluginname.js', `${values.assetBasename}.js`],
    ['pluginname.css', `${values.assetBasename}.css`],
    ['pluginname.php', values.bootstrapFilename],
    ['pluginname', values.slug],
    ['PluginName', values.className],
    ['PLUGINNAME', values.namespace],
    ['Author', values.author],
    ['pluginname_status', values.shortcode],
    ['pluginname', values.shortcodePrefix],
    ['pluginname', values.slug],
    ['pluginname', values.compactSlug],
    ['PluginName', values.displayName],
  ]);
}

function planChanges(values) {
  const replacements = buildReplacements(values);
  const changes = [];

  for (const filePath of filesIn(root)) {
    const oldRelative = relative(filePath);
    let newRelative = replaceAll(oldRelative, replacements);
    if (oldRelative === 'pluginname.php') {
      newRelative = values.bootstrapFilename;
    }
    if (newRelative !== oldRelative) {
      changes.push({ type: 'rename', from: oldRelative, to: newRelative });
    }

    if (isTextFile(filePath)) {
      const oldContent = fs.readFileSync(filePath, 'utf8');
      const newContent = replaceAll(oldContent, replacements);
      if (newContent !== oldContent) {
        changes.push({ type: 'content', filePath: oldRelative, oldContent, newContent });
      }
    }
  }

  return { replacements, changes };
}

function printPlan(values, changes) {
  console.log('\nRename summary');
  console.log(`  Display name: ${values.displayName}`);
  console.log(`  Namespace:    ${values.namespace}`);
  console.log(`  Class:        ${values.className}`);
  console.log(`  Slug:         ${values.slug}`);
  console.log(`  Prefix:       ${values.shortcodePrefix}`);
  console.log(`  Package:      ${values.packageName}`);
  console.log(`\nPlanned changes: ${changes.length}`);

  for (const change of changes) {
    if (change.type === 'rename') {
      console.log(`  rename  ${change.from} -> ${change.to}`);
    } else {
      console.log(`  update  ${change.filePath}`);
    }
  }
}

function applyChanges(changes) {
  const renames = changes
    .filter((item) => item.type === 'rename')
    .sort((left, right) => right.from.length - left.from.length);
  const destinations = new Set();

  for (const change of renames) {
    const from = path.join(root, change.from);
    const to = path.join(root, change.to);
    if (destinations.has(change.to)) {
      throw new Error(`Cannot rename ${change.from}; destination is used more than once: ${change.to}`);
    }
    destinations.add(change.to);
    if (fs.existsSync(to) && path.resolve(from) !== path.resolve(to)) {
      throw new Error(`Cannot rename ${change.from}; destination already exists: ${change.to}`);
    }
  }

  for (const change of changes.filter((item) => item.type === 'content')) {
    const filePath = path.join(root, change.filePath);
    fs.writeFileSync(filePath, change.newContent, 'utf8');
  }

  for (const change of renames) {
    const from = path.join(root, change.from);
    const to = path.join(root, change.to);
    fs.renameSync(from, to);
  }
}

async function main() {
  if (process.argv.includes('--help') || process.argv.includes('-h')) {
    console.log('Usage: npm run rename [-- --dry-run]');
    console.log('');
    console.log('Guides you through renaming the WikiPress external plugin template.');
    console.log('The default mode previews changes and asks you to type APPLY.');
    console.log('--dry-run  Preview changes without asking for confirmation or writing files.');
    return;
  }

  const dryRunOnly = process.argv.includes('--dry-run');
  const applyImmediately = process.argv.includes('--apply');
  console.log('WikiPress external plugin rename assistant');
  console.log('This tool excludes .git, vendor, node_modules, compiled output, and itself.');
  console.log('The default mode previews changes and asks for confirmation. Use --dry-run to skip confirmation or --apply to apply after the questions.');

  const values = await collectAnswers();
  const { changes } = planChanges(values);
  printPlan(values, changes);

  if (changes.length === 0) {
    console.log('\nNo template placeholders were found.');
    return;
  }

  let shouldApply = applyImmediately;
  if (!dryRunOnly && !applyImmediately) {
    const rl = readline.createInterface({ input: process.stdin, output: process.stdout });
    const answer = await ask(rl, '\nType APPLY to perform these changes, or press Enter to leave the repo untouched');
    rl.close();
    shouldApply = answer === 'APPLY';
  }

  if (!shouldApply) {
    console.log('\nDry run complete. No files were changed.');
    return;
  }

  applyChanges(changes);
  console.log('\nRename complete. Run:');
  console.log('  composer dump-autoload');
  console.log('  npm run i18n:pot');
  console.log('  npm run i18n:mo');
  console.log('  npm run build');
}

main().catch((error) => {
  console.error(`\nRename failed: ${error.message}`);
  process.exitCode = 1;
});
