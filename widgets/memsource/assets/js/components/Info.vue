<style lang="scss">
@import 'vars';

.ms-info {
    min-height: 2.75em;
    margin-bottom: 1.5em;
    padding: 0.5em;
    padding-left: 40px;
    position: relative;

    font-size: 1em;
    text-align: left;
    line-height: 1.4;
    border: 2px solid;
    background: #fff;

    &.error {
        border-color: $color-error;
        color: $color-error;
    }

    &.warning {
        border-color: $color-warning;
        color: $color-warning;
    }

    i {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        position: absolute;
            top: 0;
            left: 0;

        font-size: 1.2em;
    }
}
</style>

<template>
    <div :class="{
        'ms-info': true,
        [type]: true
    }">
        <i :class="{
            fa: true,
            [icon]: true
        }"></i>
        <slot></slot>
    </div>
</template>

<script>
module.exports = {
    props: ['type'],
    computed: {
        icon: function () {
            switch (this.type) {
                case 'error': return 'fa-exclamation-circle';
                case 'warning': return 'fa-exclamation-triangle';
                default: return 'fa-info-circle';
            }
        }
    }
};
</script>