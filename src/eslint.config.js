export default [
    {
        files: ["resources/js/**/*.js"],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: "module",
            globals: {
                console: "readonly",
                process: "readonly"
            }
        },
        rules: {
            "no-console": "warn",
            "no-unused-vars": ["warn", { argsIgnorePattern: "^_" }],
            "prefer-const": "error",
            "no-var": "error"
        }
    }
];
