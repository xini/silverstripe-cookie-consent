import autoprefixer from 'autoprefixer';
import cssnano from 'cssnano';

export default {
    plugins: [
        autoprefixer({
            cascade: false,
            supports: false
        }),

        cssnano({
            preset: 'default',
        })
    ]
};
