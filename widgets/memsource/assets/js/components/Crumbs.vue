<style lang="scss">
.ms-crumbs {
    display: flex;
    justify-content: center;
    min-height: 1em;
    margin: 1.5em 0;
    text-align: center;

    button {
        display: flex;
        font-weight: bold;

        & + button {
            &:before {
                content: '→';
                margin: 0 6px;

                color: #000;
                font-size: 15px;
                user-select: none;
                opacity: 0.5;
            }
        }

        &:hover, &:focus {
            color: #555;
        }
    }
}
</style>

<template>
    <h3 class="ms-crumbs">
        <button
            v-for="crumb in entries"
            :key="crumb.id"
            @click="$emit('click', crumb.id)"
            class="btn"
        >
            {{ crumb.text }}
        </button>
    </h3>
</template>

<script>
module.exports = {
    props: ['entries']
};
</script>