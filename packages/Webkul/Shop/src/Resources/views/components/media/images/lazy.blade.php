<v-shimmer-image {{ $attributes }}>
    <div {{ $attributes->merge(['class' => 'shimmer']) }}></div>
</v-shimmer-image>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-shimmer-image-template"
    >
        <div
            v-bind="filteredAttrs"
            :class="['shimmer', $attrs.class]"
            v-if="isLoading"
            ref="shimmer"
        >
        </div>

        <img
            v-bind="filteredAttrs"
            :src="currentSrc"
            :srcset="currentSrcset"
            v-on:load="onLoad"
            v-on:error="onError"
            v-show="! isLoading"
        >
    </script>

    <script type="module">
        app.component('v-shimmer-image', {
            template: '#v-shimmer-image-template',

            props: {
                lazy: {
                    type: Boolean,
                    default: true,
                },

                src: {
                    type: String,
                    default: '',
                },
            },

            data() {
                return {
                    isLoading: true,
                    currentSrc: '',
                    currentSrcset: '',
                };
            },

            computed: {
                filteredAttrs() {
                    const { src, srcset, ...attrs } = this.$attrs;
                    return attrs;
                }
            },

            mounted() {
                if (! this.lazy) {
                    this.currentSrc = this.src;
                    this.currentSrcset = this.$attrs.srcset || '';
                    return;
                }

                if (! window.IntersectionObserver) {
                    this.currentSrc = this.src;
                    this.currentSrcset = this.$attrs.srcset || '';
                    return;
                }

                let self = this;
                let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            self.currentSrc = self.src;
                            self.currentSrcset = self.$attrs.srcset || '';
                            observer.unobserve(entry.target);
                        }
                    });
                });

                if (this.$refs.shimmer) {
                    lazyImageObserver.observe(this.$refs.shimmer);
                } else {
                    this.$nextTick(() => {
                        if (this.$refs.shimmer) {
                            lazyImageObserver.observe(this.$refs.shimmer);
                        } else {
                            this.currentSrc = this.src;
                            this.currentSrcset = this.$attrs.srcset || '';
                        }
                    });
                }
            },

            methods: {
                onLoad() {
                    this.isLoading = false;
                },

                onError() {
                    this.isLoading = false;
                }
            },
        });
    </script>
@endpushOnce
