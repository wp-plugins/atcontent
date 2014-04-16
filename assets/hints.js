var Hint;

(function () {
    'use strict';

    Hint = function (options) {
        var self = this,
            isCompleted;

        isCompleted = false;

        this.isCompleted = function () { return isCompleted; };
        this.completenessCheck = function () { return options.completenessCheck.apply(self); };
        this.complete = function (wasCompletedInPast) {
            if (isCompleted) return;
            isCompleted = true;
            options.onComplete.call(self, wasCompletedInPast);
        };
        this.uncomplete = function () {
            if (!isCompleted) return;
            isCompleted = false;
            options.onCompleteCancel.call(self);
        };
        this.makeIteration = function () {
            options.iteration.apply(self, arguments);
        };
        this.state = options.state;

        if (this.completenessCheck()) {
            this.complete(true);
        }
    };
    Hint.prototype.iterate = function () {
        this.makeIteration.apply(this, arguments);
        if (this.completenessCheck()) {
            this.complete();
        } else {
            this.uncomplete();
        }
    };
})();