/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

const promise = Promise.reject('BBB')
    .then((v) => {
        console.log('Then', v);
    });


Promise.allSettled().then((v) => {

})
