// Запуск из корня репозитория: node tests/number-check.mjs.
import fs from 'node:fs';
const cases = JSON.parse( fs.readFileSync( new URL( './number-cases.json', import.meta.url ), 'utf8' ) );
const source = fs.readFileSync( new URL( '../plugins/argon-shop/assets/js/shared/format.js', import.meta.url ), 'utf8' );
const { parseNumber } = await import( 'data:text/javascript;base64,' + Buffer.from( source ).toString( 'base64' ) );
let failed = 0;
for ( const { input, expected } of cases ) {
    const actual = parseNumber( input );
    if ( ! Number.isFinite( actual ) || Math.abs( actual - expected ) > 1e-9 * Math.max( 1, Math.abs( expected ) ) ) {
        console.error( 'FAIL:', JSON.stringify( input ), 'expected', expected, 'got', actual );
        failed++;
    }
}
for ( const input of [ NaN, Infinity, -Infinity, undefined ] ) {
    if ( parseNumber( input ) !== 0 ) failed++;
}
if ( process.argv.includes( '--json' ) ) {
    console.log( JSON.stringify( cases.map( ( item ) => parseNumber( item.input ) ) ) );
} else {
    console.log( `${ failed ? 'FAILED' : 'OK' }: ${cases.length + 4} JavaScript number cases` );
}
process.exit( failed ? 1 : 0 );
