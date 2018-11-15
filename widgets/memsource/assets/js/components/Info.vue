<style lang="scss">
@import 'vars';

.ms-info {
    min-height: 2.75em;
    margin-bottom: 1.5em;
    padding: 0.5em 0.9em 0.5em 40px;
    position: relative;

    font-size: 1em;
    text-align: left;
    line-height: 1.4;

    background: #fff;
    border: 2px solid $color-info;
    color: $color-info;

    &.error {
        border-color: $color-error;
        color: $color-error;
    }

    &.warning {
        border-color: $color-warning;
        color: $color-warning;
    }

    &.success {
        border-color: $color-success;
        color: $color-success;
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

        font-size: 14px;
    }
}
</style>

<template>
    <div
        class="ms-info"
        :class="{
            [type]: true
        }"
    >
        <i
            class="fa"
            :class="{
                [icon]: true
            }"
        ></i>
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
                case 'success': return 'fa-check-circle';
                default: return 'fa-info-circle';
            }
        }
    }
};
</script>