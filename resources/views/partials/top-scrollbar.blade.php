{{-- 
    Partial: top-scrollbar.blade.php
    Usage:
    <div x-data="topScrollHandler()" x-init="init()" class="flex flex-col">
        @include('partials.top-scrollbar')
        <div x-ref="contentContainer" @scroll="sync($el, $refs.topScrollbar)" class="overflow-x-auto">
            <table x-ref="mainTable">...</table>
        </div>
    </div>
--}}

<div x-ref="topScrollbar" 
     @scroll="sync($el, $refs.contentContainer)" 
     class="overflow-x-auto overflow-y-hidden h-3 mb-1 invisible group-hover:visible transition-opacity"
     :class="hasHorizontalScroll ? 'visible' : 'hidden'">
    <div :style="'width: ' + scrollWidth + 'px'" class="h-3"></div>
</div>

<script>
    function topScrollHandler() {
        return {
            scrollWidth: 0,
            hasHorizontalScroll: false,
            init() {
                this.updateDimensions();
                window.addEventListener('resize', () => this.updateDimensions());
                
                // Also update when content might change (e.g. after Alpine init)
                setTimeout(() => this.updateDimensions(), 100);
            },
            updateDimensions() {
                if (this.$refs.mainTable) {
                    this.scrollWidth = this.$refs.mainTable.scrollWidth;
                    this.hasHorizontalScroll = this.$refs.mainTable.scrollWidth > this.$refs.contentContainer.clientWidth;
                }
            },
            sync(source, target) {
                if (target) {
                    target.scrollLeft = source.scrollLeft;
                }
            }
        }
    }
</script>
