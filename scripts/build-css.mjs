/* ==========================================================================
   Build: css/src/index.css and its imports, in their cascade layers, as one
   minified css/dfh-course.css. No targets, so nesting and other modern CSS
   stay as written.
   ========================================================================== */
import { bundle } from 'lightningcss';
import { writeFileSync } from 'node:fs';

const { code } = bundle({ filename: new URL('../css/src/index.css', import.meta.url).pathname, minify: true });
writeFileSync(new URL('../css/dfh-course.css', import.meta.url), code);
console.log(`css/dfh-course.css: ${(code.length / 1024).toFixed(1)} KB`);
