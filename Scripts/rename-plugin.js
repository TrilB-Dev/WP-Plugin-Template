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
  let updated = value;
  for (const [search, replacement] of replacements) {
    updated = updated.split(search).join(replacement);
  }
  return updated;
}

function relative(filePath) {
  return path.relative(root, filePath).replaceAll('\\', '/');
}

async function collectAnswers() {
  const rl = readline.createInterface({ input: process.stdin, output: process.stdout });
  try {
    const pluginName = await ask(rl, 'Plugin Name', 'PluginName');
    const pluginSlug = await ask(rl, 'Plugin Slug', 'plugin-name');
    const constantPrefix = await ask(rl, 'Plugin Constant Prefix', 'PLUGINNAME');
    const description = await ask(rl, 'Plugin Description', 'A WordPress plugin.');
    const pluginUri = await ask(rl, 'Plugin URI', `https://trilb.dev/${pluginSlug}`);
    const defaultLanguage = await ask(rl, 'Default Language', 'en_GB');
    const authorUsername = await ask(rl, 'Author User Name', 'CaptainUnderpants123');
    const authorFullName = await ask(rl, 'Author Full Name', 'Bob Marley');
    const authorEmail = await ask(rl, 'Author Email Address', 'bob@trilb.dev');
    const authorUri = await ask(rl, 'Author URI', 'https://trilb.dev');

    required(pluginName, 'Plugin Name', /^[A-Za-z_][A-Za-z0-9_]*$/, 'letters, digits, or underscores; no spaces');
    required(pluginSlug, 'Plugin Slug', /^[a-z0-9]+(?:-[a-z0-9]+)*$/, 'lowercase letters, digits, or dashes');
    required(constantPrefix, 'Plugin Constant Prefix', /^[A-Z0-9_]+$/, 'uppercase letters, digits, or underscores');
    required(description, 'Plugin Description', /\S/, 'a description');
    required(pluginUri, 'Plugin URI', /^https?:\/\/[^\s]+$/, 'a valid URL');
    required(defaultLanguage, 'Default Language', /^[a-z]{2}_[A-Z]{2}$/, 'e.g. en_GB');
    required(authorUsername, 'Author User Name', /^[A-Za-z0-9_-]+$/, 'letters, digits, underscores, or dashes');
    required(authorFullName, 'Author Full Name', /^[A-Za-z][A-Za-z .'-]*$/, 'a valid name');
    required(authorEmail, 'Author Email Address', /^[^\s@]+@[^\s@]+\.[^\s@]+$/, 'a valid email address');
    required(authorUri, 'Author URI', /^https?:\/\/[^\s]+$/, 'a valid URL');

    return {
      pluginName,
      pluginSlug,
      constantPrefix,
      description,
      pluginUri,
      defaultLanguage,
      authorUsername,
      authorFullName,
      authorEmail,
      authorUri,
      bootstrapFilename: `${pluginSlug}.php`,
      composerName: `${authorUsername}/${pluginSlug}`,
      packageName: pluginSlug,
      phpNamespace: pluginName,
    };
  } finally {
    rl.close();
  }
}

function buildReplacements(values) {
  return [
    ['PluginName', values.pluginName],
    ['PLUGINNAME', values.constantPrefix],
    ['pluginname', values.pluginSlug],
  ];
}

function updatePluginHeader(content, values) {
  const oldHeader = `/**\n * PluginName - A WordPress Plugin\n *\n * This is the main plugin file for the PluginName WordPress plugin. It contains the plugin metadata and initializes the plugin by including necessary files and setting up activation and deactivation hooks.\n *\n * Plugin Name:       PluginName\n * Plugin URI:        https://trilb.dev/collection/web-extension/wordpress/pluginname\n * Description:       PluginName is a WordPress plugin.\n * Author:            MrTrilB\n * Author URI:        https://trilb.dev\n * License:           GPL-2.0+\n * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt\n * Text Domain:       pluginname\n * Version:           1.0.0\n * Domain Path:       src/languages\n */`;

  const newHeader = `/**\n * ${values.pluginName} - A WordPress Plugin\n *\n * This is the main plugin file for the ${values.pluginName} WordPress plugin. It contains the plugin metadata and initializes the plugin by including necessary files and setting up activation and deactivation hooks.\n *\n * Plugin Name:       ${values.pluginName}\n * Plugin URI:        ${values.pluginUri}\n * Description:       ${values.description}\n * Author:            ${values.authorFullName}\n * Author URI:        ${values.authorUri}\n * License:           GPL-2.0+\n * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt\n * Text Domain:       ${values.pluginSlug}\n * Version:           1.0.0\n * Domain Path:       src/languages\n */`;

  return content.includes(oldHeader) ? content.replace(oldHeader, newHeader) : content;
}

function planChanges(values) {
  const changes = [];

  for (const filePath of filesIn(root)) {
    const oldRelative = relative(filePath);
    let newRelative = oldRelative;

    if (oldRelative === 'pluginname.php') {
      newRelative = values.bootstrapFilename;
    }

    if (oldRelative === 'src/PluginName.php') {
      newRelative = `src/${values.pluginName}.php`;
    }

    if (newRelative !== oldRelative) {
      changes.push({ type: 'rename', from: oldRelative, to: newRelative });
    }

    if (isTextFile(filePath)) {
      const oldContent = fs.readFileSync(filePath, 'utf8');
      let newContent = replaceAll(oldContent, buildReplacements(values));

      if (oldRelative === 'pluginname.php' || oldRelative === values.bootstrapFilename) {
        newContent = updatePluginHeader(newContent, values);
      }

      if (newContent !== oldContent) {
        changes.push({ type: 'content', filePath: oldRelative, oldContent, newContent });
      }
    }
  }

  return { changes };
}

function printPlan(values, changes) {
  console.log('\nRename summary');
  console.log(`  Plugin name:        ${values.pluginName}`);
  console.log(`  Plugin slug:        ${values.pluginSlug}`);
  console.log(`  Constant prefix:    ${values.constantPrefix}`);
  console.log(`  Description:        ${values.description}`);
  console.log(`  Plugin URI:         ${values.pluginUri}`);
  console.log(`  Author user:        ${values.authorUsername}`);
  console.log(`  Author full name:   ${values.authorFullName}`);
  console.log(`  Author email:       ${values.authorEmail}`);
  console.log(`  Composer package:   ${values.composerName}`);
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
    console.log('Guides you through renaming this WordPress plugin template.');
    console.log('The default mode previews changes and asks you to type APPLY.');
    console.log('--dry-run  Preview changes without asking for confirmation or writing files.');
    return;
  }

  const dryRunOnly = process.argv.includes('--dry-run');
  const applyImmediately = process.argv.includes('--apply');
  console.log('WordPress plugin rename assistant');
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
