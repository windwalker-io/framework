/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

// const promise = Promise.any(
//     [
//         Promise.resolve('A'),
//         Promise.reject('B'),
//     ]
//     )
//     .then((v) => {
//         console.log('Then', v);
//     });

const p = Promise.reject(123)
    .then(null, () => 'GGGG');

setTimeout(() => {
    console.dir(Promise.resolve(123));
}, 10);
