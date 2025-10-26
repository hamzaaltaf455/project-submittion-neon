/**
 * Simple Build Script for Landing Page
 * Minifies CSS and JavaScript files
 * 
 * Usage: node build.js
 */

const fs = require('fs');
const path = require('path');

// =====================================================
// Simple CSS Minifier
// =====================================================
function minifyCSS(css) {
    return css
        // Remove comments
        .replace(/\/\*[\s\S]*?\*\//g, '')
        // Remove whitespace
        .replace(/\s+/g, ' ')
        // Remove space around special characters
        .replace(/\s*([{}:;,>+~])\s*/g, '$1')
        // Remove trailing semicolons
        .replace(/;}/g, '}')
        .trim();
}

// =====================================================
// Simple JS Minifier
// =====================================================
function minifyJS(js) {
    return js
        // Remove single-line comments (but preserve URLs)
        .replace(/([^:])\/\/.*$/gm, '$1')
        // Remove multi-line comments
        .replace(/\/\*[\s\S]*?\*\//g, '')
        // Remove extra whitespace
        .replace(/\s+/g, ' ')
        // Remove space around operators and brackets
        .replace(/\s*([{}();,:])\s*/g, '$1')
        .trim();
}

// =====================================================
// Build Process
// =====================================================
function build() {
    console.log('🚀 Starting build process...\n');

    try {
        // Read source files
        console.log('📖 Reading source files...');
        const cssSource = fs.readFileSync(path.join(__dirname, 'css', 'styles.css'), 'utf8');
        const jsSource = fs.readFileSync(path.join(__dirname, 'js', 'script.js'), 'utf8');

        // Minify files
        console.log('🔨 Minifying CSS...');
        const cssMinified = minifyCSS(cssSource);
        
        console.log('🔨 Minifying JavaScript...');
        const jsMinified = minifyJS(jsSource);

        // Create dist directory
        const distDir = path.join(__dirname, 'dist');
        if (!fs.existsSync(distDir)) {
            fs.mkdirSync(distDir);
            console.log('📁 Created dist directory');
        }

        const distCssDir = path.join(distDir, 'css');
        const distJsDir = path.join(distDir, 'js');
        
        if (!fs.existsSync(distCssDir)) fs.mkdirSync(distCssDir);
        if (!fs.existsSync(distJsDir)) fs.mkdirSync(distJsDir);

        // Write minified files
        console.log('💾 Writing minified files...');
        fs.writeFileSync(path.join(distCssDir, 'styles.min.css'), cssMinified);
        fs.writeFileSync(path.join(distJsDir, 'script.min.js'), jsMinified);

        // Copy HTML to dist
        console.log('📋 Copying HTML...');
        const htmlSource = fs.readFileSync(path.join(__dirname, 'index.html'), 'utf8');
        const htmlProduction = htmlSource
            .replace('css/styles.css', 'css/styles.min.css')
            .replace('js/script.js', 'js/script.min.js');
        fs.writeFileSync(path.join(distDir, 'index.html'), htmlProduction);

        // Generate stats
        const originalCssSize = Buffer.byteLength(cssSource, 'utf8');
        const minifiedCssSize = Buffer.byteLength(cssMinified, 'utf8');
        const originalJsSize = Buffer.byteLength(jsSource, 'utf8');
        const minifiedJsSize = Buffer.byteLength(jsMinified, 'utf8');

        const cssSavings = ((1 - minifiedCssSize / originalCssSize) * 100).toFixed(2);
        const jsSavings = ((1 - minifiedJsSize / originalJsSize) * 100).toFixed(2);

        // Print results
        console.log('\n✅ Build complete!\n');
        console.log('📊 Build Statistics:');
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        console.log(`CSS:`);
        console.log(`  Original: ${(originalCssSize / 1024).toFixed(2)} KB`);
        console.log(`  Minified: ${(minifiedCssSize / 1024).toFixed(2)} KB`);
        console.log(`  Savings:  ${cssSavings}%`);
        console.log('');
        console.log(`JavaScript:`);
        console.log(`  Original: ${(originalJsSize / 1024).toFixed(2)} KB`);
        console.log(`  Minified: ${(minifiedJsSize / 1024).toFixed(2)} KB`);
        console.log(`  Savings:  ${jsSavings}%`);
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        console.log('\n📦 Production files created in /dist directory\n');

    } catch (error) {
        console.error('❌ Build failed:', error.message);
        process.exit(1);
    }
}

// Run build
build();
